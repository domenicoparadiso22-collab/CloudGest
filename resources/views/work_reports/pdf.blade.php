<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rapporto Intervento {{ $report->number }}</title>
    <style>
        @page { margin: 0.8cm; } /* Margini pagina ridotti */
        body { 
            font-family: sans-serif; 
            font-size: 10px; /* Font leggermente più piccolo per far stare tutto */
            color: #000; 
            line-height: 1.1;
        }
        
        /* Utility */
        .w-100 { width: 100%; }
        .w-80 { width: 80%; }
        .w-70 { width: 70%; }
        .w-50 { width: 50%; }
        .w-35 { width: 35%; }
        .w-30 { width: 30%; }
        .w-20 { width: 20%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .no-padding { padding: 0 !important; }
        
        /* Tabelle Generiche */
        table { border-collapse: collapse; border-spacing: 0; width: 100%; }
        td, th { vertical-align: top; }

        /* INTESTAZIONE */
        .header-table { margin-bottom: 15px; }
        .company-name { font-size: 14px; font-weight: bold; color: #000; margin-bottom: 2px; }
        .logo-img { max-height: 80px; max-width: 140px; } /* Logo più contenuto */

        /* TITOLI */
        .main-title { 
            font-size: 16px; font-weight: bold; color: #e30000; 
            border-bottom: 2px solid #e30000; margin-bottom: 5px; 
        }
        .sub-title { font-size: 11px; margin-bottom: 15px; }

        /* BOX CON TITOLO ROSSO */
        .red-header-box { 
            border: 1px solid #000; 
            margin-bottom: 15px;
            overflow: hidden; /* Evita sbavature */
        }
        .box-header {
            background-color: #e30000;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            padding: 10px 10px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }
        .box-content {
            padding: 8px;
            min-height: 60px;
        }

        /* FIRME SOVRAPPOSTE */
        .signature-wrapper {
            position: relative;
            height: 60px; /* Altezza fissa per non rompere la pagina */
            width: 100%;
        }
        .stamp-layer {
            position: absolute; top: 0; left: 0;
            max-height: 60px; opacity: 0.7; z-index: 1;
        }
        .signature-layer {
            position: absolute; top: 5px; left: 10px;
            max-height: 50px; z-index: 10;
        }

        /* TABELLA ARTICOLI */
        .items-table { 
            border: 1px solid #000; 
            margin-bottom: 15px;
            font-size: 10px;
        }
        .items-table th { 
            background-color: #e30000; 
            color: #ffffff;
            border-right: 1px solid #000; 
            border-bottom: 1px solid #000; 
            padding: 10px; 
            text-align: left;
        }
        .items-table td { 
            border-right: 1px solid #000; 
            padding: 4px;
        }
        /* Rimuovi bordo bottom dalle righe interne per effetto "foglio unico" */
        .items-table tr td { border-bottom: none; }
        
        /* FOOTER TABELLA (3 Col) */
        .footer-container { margin-top: 10px; }
        .footer-col { 
            border: 1px solid #000; 
            vertical-align: top;
        }
        
        /* LEGALE */
        .legal-footer {
            margin-top: 20px;
            font-size: 7px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
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

    <div class="main-title">RAPPORTO D'INTERVENTO</div>
    <div class="sub-title">n. <strong>{{ $report->number }}</strong> del {{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</div>

    <table class="red-header-box">
        <tr>
            <td class="w-50 no-padding" style="border-right: 1px solid #000;">
                <div class="box-header">DESTINATARIO</div>
                <div class="box-content">
                    <div style="font-weight: bold; font-size: 11px;">{{ $report->client->name }}</div>
                    <div>{{ $report->client->address }}</div>
                    <div>@if($report->client->vat_number)
        P.IVA: {{ $report->client->vat_number }}<br>
    @endif
    
    @if($report->client->fiscal_code)
        C.F.: {{ $report->client->fiscal_code }}
    @endif</div>
                    @if($report->client->phone)<div>Tel: {{ $report->client->phone }}</div>@endif
                    @if($report->client->email)<div>Email: {{ $report->client->email }}</div>@endif
                </div>
            </td>
            <td class="w-50 no-padding">
                <div class="box-header">TIMBRO E FIRMA CLIENTE</div>
                <div class="box-content">
                    <div class="signature-wrapper">
                        @if($report->client->client_stamp_path)
                            <img src="{{ public_path('storage/' . $report->client->client_stamp_path) }}" class="stamp-layer">
                        @endif
                        @if($report->customer_signature_path)
                            <img src="{{ public_path('storage/' . $report->customer_signature_path) }}" class="signature-layer">
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">Cod.</th>
                <th width="{{ $hidePrices ? '80%' : '50%' }}">Descrizione</th>
                <th width="10%" class="text-center">Q.tà</th>
                @if(!$hidePrices)
                    <th width="15%" class="text-right">Prezzo</th>
                    <th width="15%" class="text-right">Importo</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if($report->notes)
            <tr>
                <td></td>
                <td style="padding: 10px 4px; font-style: italic;">
                    {!! nl2br(e($report->notes)) !!}
                </td>
                <td></td>
                @if(!$hidePrices) <td></td> <td></td> @endif
            </tr>
            @endif

            {{-- RIGA VUOTA 1 CORRETTA --}}
            <tr>
                <td style="border-right: 1px solid #000;">&nbsp;</td>
                <td class="text-center"> </td>
                <td class="text-right"> </td>
                @if(!$hidePrices)
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                @endif
            </tr>
            {{-- RIGA VUOTA 2 CORRETTA --}}
            <tr>
                <td style="border-right: 1px solid #000;">&nbsp;</td>
                <td class="text-center"> </td>
                <td class="text-right"> </td>
                @if(!$hidePrices)
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                @endif
            </tr>

            @foreach($report->rows as $row)
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
            
            {{-- RIGA VUOTA 3 CORRETTA --}}
            <tr>
                <td style="border-right: 1px solid #000;">&nbsp;</td>
                <td class="text-center"> </td>
                <td class="text-right"> </td>
                @if(!$hidePrices)
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                @endif
            </tr>
            {{-- RIGA VUOTA 4 CORRETTA --}}
            <tr>
                <td style="border-right: 1px solid #000;">&nbsp;</td>
                <td class="text-center"> </td>
                <td class="text-right"> </td>
                @if(!$hidePrices)
                    <td class="text-center"> </td>
                    <td class="text-right"> </td>
                @endif
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
                <div class="box-header">TIMBRO E FIRMA UFF. TECNICO</div>
                <div class="box-content"></div>
            </td>

            @if(!$hidePrices)
            <td class="w-30 footer-col no-padding">
                <div class="box-header">RIEPILOGO</div>
                <div class="box-content" style="text-align: right; display: flex; flex-direction: column; justify-content: flex-end;">
                    <br><br>
                    <span style="font-size: 14px; font-weight: bold;">TOTALE<br>€ {{ number_format($report->rows->sum('total'), 2, ',', '.') }}</span>
                </div>
            </td>
            @else
            <td class="w-30 footer-col no-padding">
                 <div class="box-header" style="background-color: #e30000; color: #ffffff;">NOTE</div>
                 <div class="box-content"></div>
            </td>
            @endif
        </tr>
    </table>

    <div class="legal-footer">
        Ai sensi del D.Lgs. 196/2003 e GDPR UE 2016/679 i dati saranno utilizzati esclusivamente per i fini connessi ai rapporti commerciali.
        Contributo CONAI assolto ove dovuto. Vi preghiamo di controllare i Vs. dati anagrafici, la P.IVA e il Cod. Fiscale. Non ci riteniamo responsabili di eventuali errori.
        <div class="text-right" style="margin-top: 3px;">Pag. 1</div>
    </div>

</body>
</html>