<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pagamento;
use App\Models\Servico;
use App\Models\Venda;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioPagamentosController extends Controller
{
    private function filtrar(Request $request, $paginated = false)
    {
        $user = auth()->user();
        $query = Pagamento::query()
            ->with(['venda.itens.servico', 'venda.itens.produto', 'venda.funcionario'])
            ->when(!$user->is_admin && $user->empresa_id, function ($q) use ($user) {
                $q->whereHas('venda', fn($q) => $q->where('empresa_id', $user->empresa_id));
            });

        if ($request->filled('data_inicial')) {
            $query->whereDate('data', '>=', $request->data_inicial);
        }
        if ($request->filled('data_final')) {
            $query->whereDate('data', '<=', $request->data_final);
        }
        if ($request->filled('servico_id')) {
            $query->whereHas('venda.itens', function($q) use ($request) {
                $q->where('tipo_item', 'SERVICO')
                  ->where('servico_id', $request->servico_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('parceiro')) {
            $query->whereHas('venda', function($q) use ($request) {
                $q->where('parceiro', $request->parceiro);
            });
        }

        if ($paginated) {
            return $query->orderBy('data','desc')->paginate(20);
        }

        return $query->orderBy('data','desc')->get();
    }

    private function calcularTotais(Request $request)
    {
        try {
            $pagamentos = $this->filtrar($request);
            
            $totalGeral = 0;
            $totalPago = 0;
            $totalPendente = 0;
            $totaisPorServico = [];
            $totaisPorParceiro = [];
            $totaisPorStatus = [];

            foreach ($pagamentos as $pagamento) {
                $valor = $pagamento->valor ?? 0;
                $totalGeral += $valor;
                
                // Total por status
                $status = $pagamento->status ?? 'PENDENTE';
                if (!isset($totaisPorStatus[$status])) {
                    $totaisPorStatus[$status] = 0;
                }
                $totaisPorStatus[$status] += $valor;
                
                if ($status === 'PAGO') {
                    $totalPago += $valor;
                } elseif ($status === 'PENDENTE') {
                    $totalPendente += $valor;
                }
                
                // Total por parceiro
                if ($pagamento->venda) {
                    $parceiroNome = $pagamento->venda->parceiro ?: 'Não informado';
                    if (!isset($totaisPorParceiro[$parceiroNome])) {
                        $totaisPorParceiro[$parceiroNome] = 0;
                    }
                    $totaisPorParceiro[$parceiroNome] += $valor;
                    
                    // Total por serviço
                    if($pagamento->venda->itens && $pagamento->venda->itens->count() > 0) {
                        foreach ($pagamento->venda->itens as $item) {
                            if ($item->tipo_item === 'SERVICO' && $item->servico) {
                                $servicoNome = $item->servico->tipo_servico;
                                if (!isset($totaisPorServico[$servicoNome])) {
                                    $totaisPorServico[$servicoNome] = 0;
                                }
                                $totaisPorServico[$servicoNome] += $valor;
                            }
                        }
                    }
                }
            }

            return [
                'totalGeral' => $totalGeral,
                'totalPago' => $totalPago,
                'totalPendente' => $totalPendente,
                'totaisPorServico' => $totaisPorServico,
                'totaisPorParceiro' => $totaisPorParceiro,
                'totaisPorStatus' => $totaisPorStatus
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao calcular totais de pagamentos: ' . $e->getMessage());
            return [
                'totalGeral' => 0,
                'totalPago' => 0,
                'totalPendente' => 0,
                'totaisPorServico' => [],
                'totaisPorParceiro' => [],
                'totaisPorStatus' => []
            ];
        }
    }

    public function index(Request $request)
    {
        try {
            $pagamentos = $this->filtrar($request, true);
            $totais = $this->calcularTotais($request);

            $user = auth()->user();
            return Inertia::render('Relatorios/Pagamentos', [
                'filtros'   => $request->only(['data_inicial','data_final','servico_id','status','parceiro']),
                'pagamentos' => $pagamentos,
                'totais'    => $totais,
                'servicos'  => Servico::query()
                    ->when(!$user->is_admin && $user->empresa_id, fn($q) => $q->where('empresa_id', $user->empresa_id))
                    ->select('id','tipo_servico as nome')
                    ->orderBy('tipo_servico')
                    ->get(),
                'parceiros' => Venda::getParceirosDisponiveis(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no relatório de pagamentos: ' . $e->getMessage());
            
            return Inertia::render('Relatorios/Pagamentos', [
                'filtros'   => $request->only(['data_inicial','data_final','servico_id','status','parceiro']),
                'pagamentos' => ['data' => [], 'total' => 0, 'links' => []],
                'totais'    => ['totalGeral' => 0, 'totalPago' => 0, 'totalPendente' => 0, 'totaisPorServico' => [], 'totaisPorParceiro' => [], 'totaisPorStatus' => []],
                'servicos'  => [],
                'parceiros' => [],
                'errors'    => ['Erro ao carregar relatório: ' . $e->getMessage()],
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $dados = $this->filtrar($request);
            
            $filename = 'relatorio_pagamentos_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($dados) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Cabeçalho
                fputcsv($file, ['ID', 'Data', 'Cliente', 'Placa', 'Serviço', 'Parceiro', 'Valor', 'Forma Pagamento', 'Status'], ';');
                
                $totalGeral = 0;
                $totaisPorServico = [];
                $totaisPorParceiro = [];
                $totaisPorStatus = [];

                foreach ($dados as $pagamento) {
                    $valor = $pagamento->valor ?? 0;
                    $totalGeral += $valor;
                    
                    // Buscar serviço
                    $servico = '';
                    if($pagamento->venda && $pagamento->venda->itens && $pagamento->venda->itens->count() > 0) {
                        $itemServico = $pagamento->venda->itens->where('tipo_item', 'SERVICO')->first();
                        if($itemServico && $itemServico->servico) {
                            $servico = $itemServico->servico->tipo_servico;
                        }
                    }
                    
                    // Total por serviço
                    if($servico) {
                        if (!isset($totaisPorServico[$servico])) {
                            $totaisPorServico[$servico] = 0;
                        }
                        $totaisPorServico[$servico] += $valor;
                    }
                    
                    // Total por parceiro
                    if ($pagamento->venda) {
                        $parceiroNome = $pagamento->venda->parceiro ?: 'Não informado';
                        if (!isset($totaisPorParceiro[$parceiroNome])) {
                            $totaisPorParceiro[$parceiroNome] = 0;
                        }
                        $totaisPorParceiro[$parceiroNome] += $valor;
                    }
                    
                    // Total por status
                    $status = $pagamento->status ?? 'PENDENTE';
                    if (!isset($totaisPorStatus[$status])) {
                        $totaisPorStatus[$status] = 0;
                    }
                    $totaisPorStatus[$status] += $valor;
                    
                    fputcsv($file, [
                        $pagamento->id,
                        \Carbon\Carbon::parse($pagamento->data)->format('d/m/Y'),
                        $pagamento->venda->cliente_nome_completo ?? '-',
                        $pagamento->venda->cliente_placa ?? '-',
                        $servico ?: '-',
                        $pagamento->venda->parceiro ?? '-',
                        number_format($valor, 2, ',', '.'),
                        $pagamento->forma_pagamento,
                        $pagamento->status
                    ], ';');
                }
                
                // Linha de total geral
                fputcsv($file, ['', '', '', '', '', '', 'TOTAL GERAL', number_format($totalGeral, 2, ',', '.'), ''], ';');
                
                // Totais por status
                fputcsv($file, ['', '', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR STATUS', '', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorStatus as $status => $total) {
                    fputcsv($file, [$status, '', '', '', '', '', number_format($total, 2, ',', '.'), '', ''], ';');
                }
                
                // Totais por serviço
                fputcsv($file, ['', '', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR SERVIÇO', '', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorServico as $servico => $total) {
                    fputcsv($file, [$servico, '', '', '', '', '', number_format($total, 2, ',', '.'), '', ''], ';');
                }
                
                // Totais por parceiro
                fputcsv($file, ['', '', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['TOTAIS POR PARCEIRO', '', '', '', '', '', '', '', ''], ';');
                foreach($totaisPorParceiro as $parceiro => $total) {
                    fputcsv($file, [$parceiro, '', '', '', '', '', number_format($total, 2, ',', '.'), '', ''], ';');
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar relatório de pagamentos: ' . $e->getMessage());
            return back()->with('error', 'Erro ao exportar relatório: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $dados = $this->filtrar($request);
            $totais = $this->calcularTotais($request);
            
            $pdf = Pdf::loadView('relatorios.pagamentos-pdf', [
                'pagamentos' => $dados,
                'totais' => $totais,
                'filtros' => $request->only(['data_inicial','data_final','servico_id','status','parceiro']),
            ]);
            
            $filename = 'relatorio_pagamentos_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF de pagamentos: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
}
