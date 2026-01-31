<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
            margin: 3px 0;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        
        .cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .card {
            flex: 1;
            border: 1px solid #ddd;
            padding: 12px;
            margin: 0 5px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        
        .card:first-child {
            margin-left: 0;
        }
        
        .card:last-child {
            margin-right: 0;
        }
        
        .card-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .card-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .card-value.green {
            color: #10b981;
        }
        
        .card-value.red {
            color: #ef4444;
        }
        
        .card-value.blue {
            color: #3b82f6;
        }
        
        .card-value.yellow {
            color: #f59e0b;
        }
        
        .card-value.orange {
            color: #f97316;
        }
        
        .card-value.purple {
            color: #8b5cf6;
        }
        
        .card-subtitle {
            font-size: 9px;
            color: #999;
            margin-top: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
            color: #374151;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        
        table tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>Relatório Financeiro</h1>
        <p>Período: {{ \Carbon\Carbon::parse($periodo['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($periodo['data_fim'])->format('d/m/Y') }}</p>
        <p>Gerado em: {{ $gerado_em }} por {{ $usuario }}</p>
        @if(isset($filtros['tipo']) && $filtros['tipo'])
            <p>Filtro - Tipo: {{ $filtros['tipo'] }}</p>
        @endif
        @if(isset($filtros['categoria']) && $filtros['categoria'])
            <p>Filtro - Categoria: {{ $filtros['categoria'] }}</p>
        @endif
        @if(isset($filtros['status']) && $filtros['status'])
            <p>Filtro - Status: {{ $filtros['status'] }}</p>
        @endif
        @if(isset($filtros['forma_pagamento']) && $filtros['forma_pagamento'])
            <p>Filtro - Forma de Pagamento: {{ $filtros['forma_pagamento'] }}</p>
        @endif
    </div>

    <!-- Resumo Geral (Valores Pagos) -->
    <div class="section">
        <div class="section-title">Resumo Geral (Valores Pagos)</div>
        <div class="cards">
            <div class="card">
                <div class="card-label">Total de Entradas</div>
                <div class="card-value green">R$ {{ number_format($resumo['total_entradas'], 2, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="card-label">Total de Saídas</div>
                <div class="card-value red">R$ {{ number_format($resumo['total_saidas'], 2, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="card-label">Saldo</div>
                <div class="card-value {{ $resumo['saldo'] >= 0 ? 'blue' : 'red' }}">
                    R$ {{ number_format($resumo['saldo'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Valores Pendentes -->
    <div class="section">
        <div class="section-title">Valores Pendentes</div>
        <div class="cards">
            <div class="card">
                <div class="card-label">Entradas Pendentes (no período)</div>
                <div class="card-value yellow">R$ {{ number_format($resumo['total_entradas_pendentes'], 2, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="card-label">Saídas Pendentes (no período)</div>
                <div class="card-value orange">R$ {{ number_format($resumo['total_saidas_pendentes'], 2, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="card-label">Total à Receber (geral)</div>
                <div class="card-value purple">R$ {{ number_format($resumo['total_pendente'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Entradas por Categoria (Pagas) -->
    @if(count($resumo['entradas_por_categoria']) > 0)
    <div class="section">
        <div class="section-title">Entradas por Categoria (Pagas)</div>
        <table>
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumo['entradas_por_categoria'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-right" style="color: #10b981; font-weight: bold;">
                        R$ {{ number_format($item['total'], 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Saídas por Categoria (Pagas) -->
    @if(count($resumo['saidas_por_categoria']) > 0)
    <div class="section">
        <div class="section-title">Saídas por Categoria (Pagas)</div>
        <table>
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumo['saidas_por_categoria'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-right" style="color: #ef4444; font-weight: bold;">
                        R$ {{ number_format($item['total'], 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>Relatório gerado automaticamente pelo Sistema de Gestão Financeira</p>
        <p>Este documento é válido apenas para fins informativos</p>
    </div>
</body>
</html>

