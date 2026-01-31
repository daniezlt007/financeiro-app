<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Venda;
use App\Models\Servico;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioVendasController extends Controller
{
    private function filtrar(Request $request, $paginated = false)
    {
        $query = Venda::query()->with(['funcionario','itens.servico','itens.produto']);

        if ($request->filled('data_inicial')) {
            $query->whereDate('data', '>=', $request->data_inicial);
        }
        if ($request->filled('data_final')) {
            $query->whereDate('data', '<=', $request->data_final);
        }
        if ($request->filled('servico_id')) {
            $query->whereHas('itens', function($q) use ($request) {
                $q->where('tipo_item', 'SERVICO')
                  ->where('servico_id', $request->servico_id);
            });
        }
        if ($request->filled('user_id')) {
            $query->where('funcionario_id', $request->user_id);
        }
        if ($request->filled('parceiro')) {
            $query->where('parceiro', $request->parceiro);
        }

        if ($paginated) {
            return $query->orderBy('data','desc')->paginate(20);
        }

        return $query->orderBy('data','desc')->get();
    }

    private function calcularTotais(Request $request)
    {
        try {
            $vendas = $this->filtrar($request);
            
            $totalGeral = 0;
            $totaisPorVendedor = [];
            $totaisPorServico = [];
            $totaisPorParceiro = [];
            
            foreach ($vendas as $venda) {
                $valor = $venda->valor_total ?? 0;
                $totalGeral += $valor;
                
                // Quantidade por vistoriador (contagem de atendimentos)
                $vendedorNome = $venda->funcionario ? $venda->funcionario->nome_completo : 'Não informado';
                if (!isset($totaisPorVendedor[$vendedorNome])) {
                    $totaisPorVendedor[$vendedorNome] = 0;
                }
                $totaisPorVendedor[$vendedorNome] += 1;
                
                // Total por parceiro
                $parceiroNome = $venda->parceiro ?: 'Não informado';
                if (!isset($totaisPorParceiro[$parceiroNome])) {
                    $totaisPorParceiro[$parceiroNome] = 0;
                }
                $totaisPorParceiro[$parceiroNome] += $valor;
                
                // Total por serviço
                if ($venda->itens && $venda->itens->count() > 0) {
                    foreach ($venda->itens as $item) {
                        if ($item->tipo_item === 'SERVICO' && $item->servico) {
                            $servicoNome = $item->servico->tipo_servico;
                            if (!isset($totaisPorServico[$servicoNome])) {
                                $totaisPorServico[$servicoNome] = 0;
                            }
                            $totaisPorServico[$servicoNome] += $item->qtde * $item->valor_unitario;
                        }
                    }
                }
            }
            
            return [
                'totalGeral' => $totalGeral,
                'totaisPorVendedor' => $totaisPorVendedor,
                'totaisPorServico' => $totaisPorServico,
                'totaisPorParceiro' => $totaisPorParceiro
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao calcular totais: ' . $e->getMessage());
            return [
                'totalGeral' => 0,
                'totaisPorVendedor' => [],
                'totaisPorServico' => [],
                'totaisPorParceiro' => []
            ];
        }
    }

    public function index(Request $request)
    {
        try {
            $vendas = $this->filtrar($request, true); // true para paginação
            $totais = $this->calcularTotais($request);

            return Inertia::render('Relatorios/Vendas', [
                'filtros'   => $request->only(['data_inicial','data_final','servico_id','user_id','parceiro']),
                'vendas'    => $vendas,
                'totais'    => $totais,
                // 👇 alias corrige o erro de coluna inexistente
                'servicos'  => Servico::select('id','tipo_servico as nome')->orderBy('tipo_servico')->get(),
                'usuarios'  => \App\Models\Funcionario::select('id','nome_completo as name')->orderBy('nome_completo')->get(),
                'parceiros' => Venda::getParceirosDisponiveis(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no relatório de vendas: ' . $e->getMessage());
            
            return Inertia::render('Relatorios/Vendas', [
                'filtros'   => $request->only(['data_inicial','data_final','servico_id','user_id','parceiro']),
                'vendas'    => ['data' => [], 'total' => 0, 'links' => []],
                'totais'    => ['totalGeral' => 0, 'totaisPorVendedor' => [], 'totaisPorServico' => [], 'totaisPorParceiro' => []],
                'servicos'  => [],
                'usuarios'  => [],
                'parceiros' => [],
                'errors'    => ['Erro ao carregar relatório: ' . $e->getMessage()],
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $dados = $this->filtrar($request);
            
            // Criar CSV simples
            $filename = 'relatorio_vendas_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($dados) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Cabeçalho
                fputcsv($file, ['Data', 'Cliente', 'Placa', 'Chassi', 'Parceiro', 'Serviço', 'Valor', 'Vendedor'], ';');
                
                $totalGeral = 0;
                $totaisPorVendedor = [];
                $totaisPorServico = [];
                $totaisPorParceiro = [];
                
                foreach ($dados as $venda) {
                    $valor = $venda->valor_total ?? 0;
                    $totalGeral += $valor;
                    
                    // Buscar serviço
                    $servico = '';
                    if($venda->itens && $venda->itens->count() > 0) {
                        $itemServico = $venda->itens->where('tipo_item', 'SERVICO')->first();
                        if($itemServico && $itemServico->servico) {
                            $servico = $itemServico->servico->tipo_servico;
                        }
                    }
                    
                // Total por vistoriador (para CSV)
                    $vendedorNome = $venda->funcionario ? $venda->funcionario->nome_completo : 'Não informado';
                    if (!isset($totaisPorVendedor[$vendedorNome])) {
                        $totaisPorVendedor[$vendedorNome] = 0;
                    }
                $totaisPorVendedor[$vendedorNome] += 1;
                    
                    // Total por parceiro
                    $parceiroNome = $venda->parceiro ?: 'Não informado';
                    if (!isset($totaisPorParceiro[$parceiroNome])) {
                        $totaisPorParceiro[$parceiroNome] = 0;
                    }
                    $totaisPorParceiro[$parceiroNome] += $valor;
                    
                    // Total por serviço
                    if($venda->itens && $venda->itens->count() > 0) {
                        foreach ($venda->itens as $item) {
                            if ($item->tipo_item === 'SERVICO' && $item->servico) {
                                $servicoNome = $item->servico->tipo_servico;
                                if (!isset($totaisPorServico[$servicoNome])) {
                                    $totaisPorServico[$servicoNome] = 0;
                                }
                                $totaisPorServico[$servicoNome] += $item->qtde * $item->valor_unitario;
                            }
                        }
                    }
                    
                    fputcsv($file, [
                        \Carbon\Carbon::parse($venda->data)->format('d/m/Y'),
                        $venda->cliente_nome_completo ?? '-',
                        $venda->cliente_placa ?? '-',
                        $venda->cliente_chassi ?? '-',
                        $venda->parceiro ?? '-',
                        $servico ?: '-',
                        number_format($valor, 2, ',', '.'),
                        $venda->funcionario->nome_completo ?? '-'
                    ], ';');
                }
                
                // Linha de total geral
                fputcsv($file, ['', '', '', '', '', 'TOTAL GERAL', number_format($totalGeral, 2, ',', '.'), ''], ';');
                
                // Totais por vistoriador (contagem de atendimentos)
                fputcsv($file, ['', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR VISTORIADOR (ATENDIMENTOS)', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorVendedor as $vendedor => $total) {
                    fputcsv($file, [$vendedor, '', '', '', '', '', (int) $total, ''], ';');
                }
                
                // Totais por serviço
                fputcsv($file, ['', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR SERVIÇO', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorServico as $servico => $total) {
                    fputcsv($file, [$servico, '', '', '', '', '', number_format($total, 2, ',', '.'), ''], ';');
                }
                
                // Totais por parceiro
                fputcsv($file, ['', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR PARCEIRO', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorParceiro as $parceiro => $total) {
                    fputcsv($file, [$parceiro, '', '', '', '', '', number_format($total, 2, ',', '.'), ''], ';');
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $dados = $this->filtrar($request);
        $pdf = Pdf::loadView('relatorios.vendas-pdf', compact('dados'));
        return $pdf->download('relatorio_vendas.pdf');
    }
}
