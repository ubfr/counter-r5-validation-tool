namespace App\Import;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class ExcelImport implements FromCollection
{
    use Exportable;

    public function collection()
    {
        return Invoice::all();
    }
}