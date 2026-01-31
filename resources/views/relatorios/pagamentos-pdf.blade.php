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
    <h2>Relatório de Pagamentos</h2>
    
    @if(isset($filtros) && (isset($filtros['data_inicial']) || isset($filtros['data_final'])))
    <p>
        <strong>Período:</strong>
        @if(isset($filtros['data_inicial']))
            De {{ \Carbon\Carbon::parse($filtros['data_inicial'])->format('d/m/Y') }}
        @endif
        @if(isset($filtros['data_final']))
            até {{ \Carbon\Carbon::parse($filtros['data_final'])->format('d/m/Y') }}
        @endif
    </p>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Placa</th>
                <th>Serviço</th>
                <th>Parceiro</th>
                <th>Valor</th>
                <th>Forma Pagamento</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                @php
                    $valor = $pagamento->valor ?? 0;
                    
                    // Buscar o primeiro serviço da venda
                    $servico = '';
                    if($pagamento->venda && $pagamento->venda->itens && $pagamento->venda->itens->count() > 0) {
                        $itemServico = $pagamento->venda->itens->where('tipo_item', 'SERVICO')->first();
                        if($itemServico && $itemServico->servico) {
                            $servico = $itemServico->servico->tipo_servico;
                        }
                    }
                @endphp
                <tr>
                    <td>#{{ $pagamento->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($pagamento->data)->format('d/m/Y') }}</td>
                    <td>{{ $pagamento->venda->cliente_nome_completo ?? '-' }}</td>
                    <td>{{ $pagamento->venda->cliente_placa ?? '-' }}</td>
                    <td>{{ $servico ?: '-' }}</td>
                    <td>{{ $pagamento->venda->parceiro ?? '-' }}</td>
                    <td class="total-cell">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                    <td>{{ $pagamento->forma_pagamento }}</td>
                    <td>{{ $pagamento->status }}</td>
                </tr>
            @endforeach
            
            <!-- Linha de Total -->
            <tr class="total-row">
                <td colspan="6"><strong>TOTAL GERAL:</strong></td>
                <td class="total-cell"><strong>R$ {{ number_format($totais['totalGeral'] ?? 0, 2, ',', '.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Totais por Status -->
    @if(isset($totais['totaisPorStatus']) && count($totais['totaisPorStatus']) > 0)
    <h3 style="margin-top: 30px;">Total por Status</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totais['totaisPorStatus'] as $status => $total)
                <tr>
                    <td>{{ $status }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- Totais por Serviço -->
    @if(isset($totais['totaisPorServico']) && count($totais['totaisPorServico']) > 0)
    <h3 style="margin-top: 30px;">Total por Serviço</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Serviço</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totais['totaisPorServico'] as $servico => $total)
                <tr>
                    <td>{{ $servico }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <!-- Totais por Parceiro -->
    @if(isset($totais['totaisPorParceiro']) && count($totais['totaisPorParceiro']) > 0)
    <h3 style="margin-top: 30px;">Total por Parceiro</h3>
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Parceiro</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totais['totaisPorParceiro'] as $parceiro => $total)
                <tr>
                    <td>{{ $parceiro }}</td>
                    <td class="total-cell">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    
    <div style="margin-top: 20px; font-size: 10px; color: #666;">
        Relatório gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
