<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Cliente</th>
            <th>Serviço</th>
            <th>Valor</th>
            <th>Vendedor</th>
        </tr>
    </thead>
    <tbody>
        @php $totalGeral = 0; @endphp
        @foreach($vendas as $venda)
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
                <td>{{ $servico ?: '-' }}</td>
                <td>{{ number_format($valor, 2, ',', '.') }}</td>
                <td>{{ $venda->funcionario->nome_completo ?? '-' }}</td>
            </tr>
        @endforeach
        
        <!-- Linha de Total -->
        <tr>
            <td colspan="3"><strong>TOTAL GERAL:</strong></td>
            <td><strong>{{ number_format($totalGeral, 2, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>

<!-- Totais por Vendedor -->
<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Vendedor</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totaisPorVendedor = [];
            foreach($vendas as $venda) {
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
                <td>{{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Totais por Serviço -->
<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Serviço</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totaisPorServico = [];
            foreach($vendas as $venda) {
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
                <td>{{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
