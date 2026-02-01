<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fattura {{ $invoice->number }}</title>
    <style>
        @page { margin: 0.8cm; }
        body { font-family: sans-serif; font-size: 10px; color: #000; line-height: 1.1; }
        .w-100 { width: 100%; } 
        .w-80 { width: 80%; } 
        .w-70 { width: 70%; } 
        .w-50 { width: 50%; } 
        .w-35 { width: 35%; } 
        .w-30 { width: 30%; }
        .w-20 { width: 20%; }
        
        .text-right { text-align: right; } .text-center { text-align: center; } .font-bold { font-weight: bold; } .no-padding { padding: 0 !important; }
        table { border-collapse: collapse; border-spacing: 0; width: 100%; } td, th { vertical-align: top; }
        
        .header-table { margin-bottom: 15px; }
        .company-name { font-size: 14px; font-weight: bold; color: #000; margin-bottom: 2px; }
        .logo-img { max-height: 80px; max-width: 140px; }

        .main-title { font-size: 16px; font-weight: bold; color: #e30000; border-bottom: 2px solid #e30000; margin-bottom: 5px; }
        .sub-title { font-size: 11px; margin-bottom: 15px; }

        .red-header-box { border: 1px solid #000; margin-bottom: 15px; }
        .box-header { background-color: #e30000; color: #ffffff; font-weight: bold; font-size: 9px; padding: 10px 10px; border-bottom: 1px solid #000; text-transform: uppercase; }
        .box-content { padding: 8px; min-height: 60px; }

        .items-table { border: 1px solid #000; margin-bottom: 15px; font-size: 10px; }
        .items-table th { background-color: #e30000; color: #ffffff; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: left; }
        .items-table td { border-right: 1px solid #000; padding: 4px; }
        .items-table tr td { border-bottom: none; }
        
        .footer-col { border: 1px solid #000; vertical-align: top; }
        .footer-table {
        width: 100%; /* Risolve problema 3: Forza l'allineamento con la tabella sopra */
        border-collapse: collapse;
        border: 1px solid #000;
        page-break-inside: avoid; /* Evita che si spezzi su due pagine */
    }
        .legal-footer { margin-top: 20px; font-size: 7px; color: #555; border-top: 1px solid #ccc; padding-top: 5px; }
        /* Utility per allineamenti */
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .w-33 { width: 33.33%; }
    .w-10 { width: 10%; }
    .w-15 { width: 15%; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="w-80 text-right">
                <div class="company-name">{{ $settings->company_name ?? 'Ragione Sociale' }}</div>
                <div>{{ $settings->subtitle ?? '' }}</div>
                <div>{{ $settings->address ?? '' }}</div>
                <div>P.IVA: {{ $settings->vat_number ?? '' }} - C.F.: {{ $settings->fiscal_code ?? '' }}</div>
                <div>Email: {{ $settings->email ?? '' }}</div>
                <div>PEC: {{ $settings->pec ?? '' }} | Tel: {{ $settings->phone ?? '' }}</div>
            </td>
            <td class="w-20 text-right">
                @if($settings && $settings->logo_path)
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <div class="main-title">FATTURA</div>
    <div class="sub-title">
        n. <strong>{{ $invoice->number }}</strong> del {{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}
    </div>

    <table class="red-header-box">
        <tr>
            <td class="w-50 no-padding" style="border-right: 1px solid #000;">
    <div class="box-header">CLIENTE / INTESTATARIO</div>
    <div class="box-content">
        <div style="font-weight: bold; font-size: 11px;">{{ $invoice->client->name }}</div>
        <div>{{ $invoice->client->address }}</div>
        
        <div style="margin-top: 0px;">
            @if($invoice->client->vat_number)
                <div>P.IVA: {{ $invoice->client->vat_number }}</div>
            @endif
            
            @if($invoice->client->fiscal_code)
                <div>C.F.: {{ $invoice->client->fiscal_code }}</div>
            @endif
        </div>

        <div style="margin-top: 0px;">
            @if($invoice->client->email)
                <div>Email: {{ $invoice->client->email }}</div>
            @endif

            @if($invoice->client->phone)
                <div>Tel: {{ $invoice->client->phone }}</div>
            @endif
        </div>
    </div>
</td>
            <td class="w-50 no-padding">
                <div class="box-header">DATI PAGAMENTO</div>
                <div class="box-content">
                    <div><strong>Metodo:</strong> {{ $invoice->payment_method ?? 'Rimessa Diretta' }}</div>
                    @if($invoice->due_date)
                        <div><strong>Scadenza:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                    @endif
                    </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="w-10">#</th>
                <th>Descrizione</th>
                <th class="w-10 text-center">Q.tà</th>
                <th class="w-15 text-right">Prezzo</th>
                <th class="w-15 text-right">Importo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->rows as $row)
            <tr style="{{ $row->description == '' ? 'height: 20px;' : '' }}">
                
                <td style="color: #666;">
                    {{ $row->description != '' ? $loop->iteration : '' }}
                </td>
                
                <td>
                    <strong>{!! nl2br(e($row->description)) !!}</strong>
                </td>

                <td class="text-center">
                    {{ $row->quantity > 0 ? $row->quantity : '' }}
                </td>

                <td class="text-right">
                    {{ $row->price != 0 ? '€ ' . number_format($row->price, 2, ',', '.') : '' }}
                </td>

                <td class="text-right">
                    {{ $row->total != 0 ? '€ ' . number_format($row->total, 2, ',', '.') : '' }}
                </td>
            </tr>
            @endforeach

            @for($i = 0; $i < (8 - count($invoice->rows)); $i++)
            <tr>
                <td style="color: white;">.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <table class="w-100" style="border-spacing: 5px 0; margin-left: 0px; margin-right: 0px;">
        <tr>
            <td class="w-35 footer-col no-padding">
                <div class="box-header">SCADENZE</div>
                <div class="box-content">
                    @if($invoice->due_date)
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}: € {{ number_format($invoice->total_gross, 2, ',', '.') }}
                    @endif
                </div>
            </td>

            <td class="w-35 footer-col no-padding">
                <div class="box-header">RIEPILOGO IVA</div>
                <div class="box-content" style="font-size: 9px;">
                   Totale Imponibile: € {{ number_format($invoice->total_net, 2, ',', '.') }}<br>
                   Totale Imposta: € {{ number_format($invoice->total_vat, 2, ',', '.') }}
                </div>
            </td>

            <td class="w-30 footer-col no-padding">
                <div class="box-header">TOTALE DA PAGARE</div>
                <div class="box-content" style="text-align: right; display: flex; flex-direction: column; justify-content: flex-end;">
                    <br><br>
                    <span style="font-size: 16px; font-weight: bold;">€ {{ number_format($invoice->total_gross, 2, ',', '.') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="legal-footer">
        Documento privo di valenza fiscale ai sensi dell'art. 21 DPR 633/72. L'originale è disponibile nell'area riservata dell'Agenzia delle Entrate.
        <div class="text-right" style="margin-top: 3px;">Pag. 1</div>
    </div>

</body>
</html>