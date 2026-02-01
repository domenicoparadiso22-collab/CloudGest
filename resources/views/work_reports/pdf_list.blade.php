<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Elenco Documenti</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        
        /* Intestazione Azienda */
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 16px; font-weight: bold; }
        
        /* Titolo Documento */
        .doc-title { 
            font-size: 18px; font-weight: bold; color: #000; 
            text-align: center; margin-bottom: 20px; text-transform: uppercase;
        }

        /* Tabella */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { 
            background-color: #ffffff; 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
            font-weight: bold;
        }
        td { 
            border: 1px solid #000; 
            padding: 8px; 
            vertical-align: top; 
        }

        /* Totali */
        .total-row td { background-color: #f9f9f9; font-weight: bold; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'La Tua Azienda' }}</div>
        <div>{{ $settings->address ?? '' }} - P.IVA: {{ $settings->vat_number ?? '' }}</div>
    </div>

    <div class="doc-title">Elenco Documenti</div>

    <table>
        <thead>
            <tr>
                <th width="10%">N. Doc/Anno</th>
                <th width="10%">Data</th>
                <th width="15%">Tipologia</th>
                <th width="20%">Cliente</th>
                <th width="20%">Note</th>
                <th width="15%">Stato</th>
                <th width="10%" class="text-right">Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr>
                <td>{{ $report->number }}/{{ \Carbon\Carbon::parse($report->date)->format('Y') }}</td>
                
                <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                
                <td>Rapporto d'intervento</td>
                
                <td>{{ $report->client->name }}</td>
                
                <td>{{ \Illuminate\Support\Str::limit($report->notes, 50) }}</td>
                
                <td style="font-style: italic; color: #555;">seguirà doc. di vendita</td>
                
                <td class="text-right">€ {{ number_format($report->rows->sum('total'), 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTALE COMPLESSIVO</td>
                <td class="text-right">
                    € {{ number_format($reports->sum(function($r){ return $r->rows->sum('total'); }), 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #666;">
        Documento generato il {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>