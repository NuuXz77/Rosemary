<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\StocksExport;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class ExportController extends Controller
{
    public function exportStocks(Request $request)
    {
        if (!auth()->user() || (!auth()->user()->can('reports.stocks.view') && !auth()->user()->can('reports.stocks.export'))) {
            abort(403);
        }

        return ExcelFacade::download(new StocksExport, 'stocks.xlsx');
    }
}
