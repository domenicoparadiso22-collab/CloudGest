<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Preventivo {{ $quote->number }}</title>
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
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .no-padding { padding: 0 !important; }
        
        table { border-collapse: collapse; border-spacing: 0; width: 100%; }
        td, th { vertical-align: top; }

        /* INTESTAZIONE */
        .header-table { margin-bottom: 15px; }
        .company-name { font-size: 14px; font-weight: bold; color: #000; margin-bottom: 2px; }
        .logo-img { max-height: 80px; max-width: 140px; }

        /* TITOLI */
        .main-title { 
            font-size: 16px; font-weight: bold; color: #e30000; 
            border-bottom: 2px solid #e30000; margin-bottom: 5px; 
        }
        .sub-title { font-size: 11px; margin-bottom: 15px; }

        /* BOX ROSSI STILE DANEA */
        .red-header-box { border: 1px solid #000; margin-bottom: 15px; }
        .box-header {
            background-color: #e30000; color: #ffffff;
            font-weight: bold; font-size: 9px; padding: 10px 10px;
            border-bottom: 1px solid #000; text-transform: uppercase;
        }
        .box-content { padding: 8px; min-height: 60px; }

        /* TABELLA ARTICOLI */
        .items-table { border: 1px solid #000; margin-bottom: 15px; font-size: 10px; }
        .items-table th { 
            background-color: #e30000; color: #ffffff; border-right: 1px solid #000; 
            border-bottom: 1px solid #000; padding: 10px; text-align: left;
        }
        .items-table td { border-right: 1px solid #000; padding: 4px; }
        .items-table tr td { border-bottom: none; } /* No righe interne */
        
        /* FOOTER */
        .footer-col { border: 1px solid #000; vertical-align: top; }
        .legal-footer { margin-top: 20px; font-size: 7px; color: #555; border-top: 1px solid #ccc; padding-top: 5px; }
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

    <div class="main-title">PREVENTIVO</div>
    <div class="sub-title">
        n. <strong>{{ $quote->number }}</strong> del {{ \Carbon\Carbon::parse($quote->date)->format('d/m/Y') }}
        @if($quote->valid_until)
            - Valido fino al: {{ \Carbon\Carbon::parse($quote->valid_until)->format('d/m/Y') }}
        @endif
    </div>

    <table class="red-header-box">
        <tr>
            <td class="w-50 no-padding" style="border-right: 1px solid #000;">
                <div class="box-header">DESTINATARIO</div>
                <div class="box-content">
                    <div style="font-weight: bold; font-size: 11px;">{{ $quote->client->name }}</div>
                    <div>{{ $quote->client->address }}</div>
                    <div>@if($quote->client->vat_number)
        P.IVA: {{ $quote->client->vat_number }}<br>
    @endif
    
    @if($quote->client->fiscal_code)
        C.F.: {{ $quote->client->fiscal_code }}
    @endif</div>
                    @if($quote->client->phone)<div>Tel: {{ $quote->client->phone }}</div>@endif
                    @if($quote->client->email)<div>Email: {{ $quote->client->email }}</div>@endif
                </div>
            </td>
            <td class="w-50 no-padding">
                <div class="box-header">RIFERIMENTI OFFERTA</div>
                <div class="box-content">
                    </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">Cod.</th>
                <th width="50%">Descrizione</th>
                <th width="10%" class="text-center">Q.tà</th>
                @if(!$hidePrices)
                    <th width="15%" class="text-right">Prezzo</th>
                    <th width="15%" class="text-right">Importo</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if($quote->notes)
            <tr>
                <td></td>
                <td style="padding: 10px 4px; font-style: italic;">{!! nl2br(e($quote->notes)) !!}</td>
                <td></td>
                @if(!$hidePrices) <td></td> <td></td> @endif
            </tr>
            @endif

             <tr>
                    <td style="border-right: 1px solid #000;">  </td>
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
            </tr>

            @foreach($quote->rows as $row)
                <tr>
                    <td class="text-center">{{ $row->id }}</td>
                    <td>{{ $row->description }}</td>
                    <td class="text-center">{{ $row->quantity }} {{ $row->unit }}</td>
                    @if(!$hidePrices)
                        <td class="text-right">€ {{ number_format($row->price, 2, ',', '.') }}</td>
                        <td class="text-right">€ {{ number_format($row->total, 2, ',', '.') }}</td>
                    @endif
                </tr>
            @endforeach
            
             <tr>
                    <td style="border-right: 1px solid #000;">  </td>
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
            </tr>
        </tbody>
    </table>

    <table class="w-100" style="border-spacing: 5px 0; margin-left: 0px; margin-right: 0px;">
        <tr>
            <td class="w-35 footer-col no-padding">
                <div class="box-header">TIMBRO AZIENDA</div>
                <div class="box-content text-center">
                    @if($settings->stamp_path)
                        <img src="{{ public_path('storage/' . $settings->stamp_path) }}" style="max-height: 60px; max-width: 90%;">
                    @endif
                </div>
            </td>

            <td class="w-35 footer-col no-padding">
                <div class="box-header">PER ACCETTAZIONE PREVENTIVO</div>
                <div class="box-content">
                    <br><br><br>
                    <div style="border-top: 1px dotted #000; width: 80%; margin: 0 auto; text-align: center; font-size: 8px;">Timbro e Firma</div>
                </div>
            </td>

            @if(!$hidePrices)
            <td class="w-30 footer-col no-padding">
                <div class="box-header">RIEPILOGO</div>
                <div class="box-content" style="text-align: right; display: flex; flex-direction: column; justify-content: flex-end;">
                    <br><br>
                    <span style="font-size: 14px; font-weight: bold;">TOTALE<br>€ {{ number_format($quote->rows->sum('total'), 2, ',', '.') }}</span>
                </div>
            </td>
            @else
            <td class="w-30" style="border: none;"></td>
            @endif
        </tr>
    </table>

    <div class="legal-footer">
        La presente offerta è valida fino alla data indicata. I prezzi si intendono IVA esclusa (salvo diversa indicazione).
        Ai sensi del D.Lgs. 196/2003 e GDPR UE 2016/679 i dati saranno utilizzati esclusivamente per i fini connessi ai rapporti commerciali.
        <div class="text-right" style="margin-top: 3px;">Pag. 1</div>
    </div>

</body>
</html>