<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .total-row { background: #f8f9fa; font-weight: bold; }
        .total-cell { text-align: right; }
    </style>
</head>
<body>
    <h2>Relatório de Vendas</h2>
    
    @php
        $totalGeral = 0;
    @endphp
    
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Cliente</th>
                <th>Placa</th>
                <th>Chassi</th>
                <th>Parceiro</th>
                <th>Serviço</th>
                <th>Valor</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $venda)
                @php
                    $valor = $venda->valor_total ?? 0;
                    $totalGeral += $valor;
                    
                    // Buscar o primeiro serviço da venda
                    $servico = '';
                    if($venda->itens && $venda->itens->count() > 0) {
                        $itemServico = $venda->itens->where('tipo_item', 'SERVICO')->first();
                        if($itemServico && $itemServico->servico) {
                            $servico = $itemServico->servico->tipo_servico;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($venda->data)->format('d/m/Y') }}</td>
                    <td>{{ $venda->cliente_nome_completo ?? '-' }}</td>
                    <td>{{ $venda->cliente_placa ?? '-' }}</td>
                    <td>{{ $venda->cliente_chassi ?? '-' }}</td>
                    <td>{{ $venda->parceiro ?? '-' }}</td>
                    <td>{{ $servico ?: '-' }}</td>
                    <td class="total-cell">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                    <td>{{ $venda->funcionario->nome_completo ?? '-' }}</td>
                </tr>
            @endforeach
            
            <!-- Linha de Total -->
            <tr class="total-row">
                <td colspan="6"><strong>TOTAL GERAL:</strong></td>
                <td class="total-cell"><strong>R$ {{ number_format($totalGeral, 2, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Totais por Vendedor -->
    <h3 style="margin-top: 30px;">Total por Vendedor</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totaisPorVendedor = [];
                foreach($dados as $venda) {
                    $vendedorNome = $venda->funcionario ? $venda->funcionario->nome_completo : 'Não informado';
                    if (!isset($totaisPorVendedor[$vendedorNome])) {
                        $totaisPorVendedor[$vendedorNome] = 0;
                    }
                    $totaisPorVendedor[$vendedorNome] += $venda->valor_total ?? 0;
                }
            @endphp
            @foreach($totaisPorVendedor as $vendedor => $total)
                <tr>
                    <td>{{ $vendedor }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totais por Serviço -->
    <h3 style="margin-top: 30px;">Total por Serviço</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Serviço</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totaisPorServico = [];
                foreach($dados as $venda) {
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
                }
            @endphp
            @foreach($totaisPorServico as $servico => $total)
                <tr>
                    <td>{{ $servico }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totais por Parceiro -->
    <h3 style="margin-top: 30px;">Total por Parceiro</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Parceiro</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totaisPorParceiro = [];
                foreach($dados as $venda) {
                    $parceiroNome = $venda->parceiro ?: 'Não informado';
                    if (!isset($totaisPorParceiro[$parceiroNome])) {
                        $totaisPorParceiro[$parceiroNome] = 0;
                    }
                    $totaisPorParceiro[$parceiroNome] += $venda->valor_total ?? 0;
                }
            @endphp
            @foreach($totaisPorParceiro as $parceiro => $total)
                <tr>
                    <td>{{ $parceiro }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; font-size: 10px; color: #666;">
        Relatório gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
