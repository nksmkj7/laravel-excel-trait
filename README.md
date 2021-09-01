# laravel-excel-import-trait

# About
This trait is the wrapper of the laravel-excel import function. In order to make this trait workable, laravel-excel package must be installed in your system.\
If you haven't installed it, you can install it via: 
```php
composer require maatwebsite/excel
```
This trait helps you to skip rows that don't satisfy specified rules in rules function and generated separated excel file of skipped rows with reasons.
# Usage
Following steps should be followed in order to make it work properly: \
1. Add route to download excel with skipped rows in your route file.
```php
Route::get('download-rejected-data/{fileName}', 'ExportRejectedDataController@downloadExcel');
```
2. Create a controller to download export rejected data excel.
```php
class ExportRejectedDataController extends Controller
{
    public function downloadExcel($fileName){
        return response()->download(storage_path('app/rejected-excels/' . $fileName))->deleteFileAfterSend(true);
    }
}
```
3. Place ImportExcel.php file inside App/Traits folder.
4. Use ImportExcel trait in your module specific laravel-excel import class. For this guide we are going to import stations in our database through excel.
```php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\Station;
use App\Traits\ImportExcel;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StationImport implements ToCollection, WithHeadingRow
{
    use Importable, ImportExcel;
}
```
5. Overwrite the collection function and set model and call insert function.
```php
public function collection(Collection $rows)
{
    $this->setModel(Station::class);
    return $this->insert($rows);
}
```

6. Add script to your index page. i.e page to redirect after completion of import
```javascript
<script>
    $('document').ready(function(){
    var excelPath = "{{session('rejected_data_url') ?? ""}}";
    if (excelPath)
        window.location.replace(excelPath);
    });

</script>
```
# How does it work ?
Brief description of trait's functions
| Function name | Arguments | Return value| Description
| ----------- | ----------- |----------- |----------- |
| rules | - | array | Laravel validation rules are returned as an array. Each row will get validated against those rules.
| messages | - | array | Custom messages for laravel validation rules.
| setModel | file path of model | - | Setup the model required for operation.
| getModel | - | instance of model | Get the model instance.
| getMessageBag | - | instance of messageBag | Collect the messages.
| totalRowsCount | collection of all rows | total row count of collection. | -
| headers | - | array | Headers for export excel of discarded rows.
| rejectedExcelDownloadUrl | - | string | Export discarded excel download url is set here.   ``` default: url('system/download-rejected-data/')```.
| prepareCsvData | collection/array | array | accept individual row and convert row data to array if it is a collection and return transformed data. 
| transform | array | array | Transformed data in order to store in database and return transformed data.
| validate | 1. array 2.callback | boolean | Validate individual row data against rule and execute callback if any.
| insert | collection | boolean | Accepts excel rows collection. Execute all the trait function in order to validate and insert data to database.
| exportRejectedData | array | boolean | Accept headers array and return true if there are rejected row, otherwise false.
| create | array | collection | Insert validated and transformed data into database. For extra logic, one needs to overwrite this function.

>Note: This trait only supports importing to collections. If trait is insufficient for your purpose, laravel-excel has its own row validation features. One can modify as per his/her needs.