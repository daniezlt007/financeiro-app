<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venda #{{ $venda->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            font-size: 12px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .logo {
            color: #00A651;
            font-size: 32px;
            font-weight: bold;
        }
        
        .company {
            text-align: right;
            font-size: 11px;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .title {
            text-align: center;
            color: #00A651;
            font-size: 24px;
            font-weight: bold;
            margin: 30px 0;
        }
        
        .info-line {
            margin: 8px 0;
            font-size: 12px;
        }
        
        .info-line strong {
            font-weight: bold;
        }
        
        .signature {
            margin-top: 60px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            margin: 40px auto 10px;
            padding-top: 5px;
            text-align: center;
        }
        
        .amount {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        
        .sacos {
            color: #00A651;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">DEKRA</div>
        <div class="company">
            <div class="company-name">PROSERV - PRESTAÇÃO DE SERVIÇO LTDA</div>
            <div>03.892.511/0001-53 - TV CURUZU, 566</div>
            <div>PEDREIRA - BELEM PA - CEP 66085-110</div>
            <div>Tel.: (91) 3242-0105</div>
        </div>
    </div>
    
    <div style="text-align: center; margin: 10px 0; font-size: 11px;">
        SAC: (91) 99330-2224 - fr674diretoria@yahoo.com.br - www.dekra.com.br
    </div>
    
    <!-- Título RECIBO -->
    <div class="title">RECIBO</div>
    
    <!-- Conteúdo -->
    <div style="margin: 20px 0;">
        <div class="info-line">
            Recebemos de <strong>{{ $venda->cliente_nome_completo }}</strong>
            @if($venda->cliente_cpf_cnpj)
                - CPF/CNPJ: {{ $venda->cliente_cpf_cnpj }}
            @endif
        </div>
        
        <div class="info-line">
            @if($venda->cliente_telefone)
                Telefone: {{ $venda->cliente_telefone }}
            @endif
            @if($venda->cliente_placa)
                - Placa: {{ $venda->cliente_placa }}
            @endif
        </div>
        
        <div class="info-line" style="margin-top: 20px;">
            A importância de <strong>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</strong>
        </div>
        
        <div class="info-line" style="margin-top: 10px;">
            Referente a:
        </div>
        
        <!-- Itens da venda -->
        <div style="margin: 10px 0 20px 20px;">
            @foreach($venda->itens as $item)
                @php
                    $nome = '';
                    if($item->tipo_item === 'SERVICO' && $item->servico) {
                        $nome = $item->servico->tipo_servico;
                    } elseif($item->tipo_item === 'PRODUTO' && $item->produto) {
                        $nome = $item->produto->nome;
                    }
                @endphp
                <div class="info-line">
                    - {{ $nome }} - Qtd: {{ $item->qtde }} - R$ {{ number_format($item->valor_unitario * $item->qtde, 2, ',', '.') }}
                </div>
            @endforeach
        </div>
        
        <div class="info-line" style="margin-top: 20px;">
            <div class="amount">
                <strong>VALOR TOTAL: R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</strong>
            </div>
        </div>
        
        @if($venda->consumidor_tipo)
        <div class="info-line">
            Tipo de Consumidor: {{ $venda->consumidor_tipo }}
        </div>
        @endif
        
        @if($venda->funcionario)
        <div class="info-line">
            Responsável: {{ $venda->funcionario->nome_completo }}
        </div>
        @endif
        
        @if($venda->user)
        <div class="info-line">
            Registrado por: <strong>{{ $venda->user->name }}</strong>
        </div>
        @endif
    </div>
    
    <!-- Data e Assinatura -->
    <div class="info-line" style="margin-top: 40px;">
        Belém, {{ \Carbon\Carbon::parse($venda->data)->format('d') }} de {{ \Carbon\Carbon::parse($venda->data)->locale('pt_BR')->monthName }} de {{ \Carbon\Carbon::parse($venda->data)->format('Y') }}
    </div>
    
    <div class="signature">
        <div class="signature-line"></div>
        <div>PROSERV PRESTAÇÃO DE SERVIÇOS LTDA</div>
    </div>
    
    @if($venda->observacoes)
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 11px;">
        <strong>Observações:</strong> {{ $venda->observacoes }}
    </div>
    @endif
    
    <!-- Hash de Verificação -->
    @if(isset($hash_verificacao))
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 9px; color: #666;">
        <div><strong>Código de Verificação:</strong></div>
        <div style="font-family: monospace; letter-spacing: 2px; margin-top: 5px;">{{ strtoupper($hash_verificacao) }}</div>
        <div style="margin-top: 5px; font-size: 8px;">Documento autenticado digitalmente</div>
    </div>
    @endif
</body>
</html>

