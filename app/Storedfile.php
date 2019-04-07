<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Storedfile extends Model
{

    const SOURCE_FILE_VALIDATE = 1;

    const SOURCE_SUSHI_VALIDATE = 2;

    const SOURCE_CONSORTIUM_TOOL = 3;

    const TYPE_ERROR_MODEL = 1;

    const TYPE_SERVICE_STATUS = 2;

    const TYPE_MEMBER_LIST = 3;

    const TYPE_REPORT_LIST = 4;

    const TYPE_REPORT = 5;

    const TYPE_CHECK_RESULT = 6;

    protected static $sourceNames = [
        self::SOURCE_FILE_VALIDATE => 'File Validate',
        self::SOURCE_SUSHI_VALIDATE => 'SUSHI Validate',
        self::SOURCE_CONSORTIUM_TOOL => 'Consortium Tool'
    ];

    protected static $typeNames = [
        self::TYPE_ERROR_MODEL => 'Error Model',
        self::TYPE_SERVICE_STATUS => 'Service Status',
        self::TYPE_MEMBER_LIST => 'Member List',
        self::TYPE_REPORT_LIST => 'Report List',
        self::TYPE_REPORT => 'Report',
        self::TYPE_CHECK_RESULT => 'Validation Result'
    ];

    protected static $extension2contenttype = [
        'csv' => 'text/csv',
        'json' => 'application/json',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'tsv' => 'text/tab-separated-values',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    public function getSourceNames()
    {
        return $this->sourceNames;
    }

    public function getTypeNames()
    {
        return $this->typeNames;
    }

    public function reportfile()
    {
        return $this->hasOne('App\Reportfile', 'reportfile_id');
    }

    public function checkresult()
    {
        return $this->hasOne('App\Checkresult', 'resultfile_id');
    }

    public function delete()
    {
        Storage::delete($this->location);

        return parent::delete();
    }

    public static function store($file, $filename, $source, $type, $userId)
    {
        if (! is_string($file) && ! ($file instanceof File) && ! ($file instanceof UploadedFile)) {
            throw new \InvalidArgumentException("file invalid, expecting string, File or UploadedFile");
        }
        if (! isset(self::$sourceNames[$source])) {
            throw new \InvalidArgumentException("source {$source} not within the allowed range");
        }
        if (! isset(self::$typeNames[$type])) {
            throw new \InvalidArgumentException("type {$type} not within the allowed range");
        }
        $user = User::where('id', $userId)->firstOrFail();

        $location = Storage::put('storedfiles', $file);
        if ($location === false) {
            throw new \Exception("failed to store uploaded file");
        }

        $storedfile = new Storedfile();
        $storedfile->user_id = $user->id;
        $storedfile->source = $source;
        $storedfile->type = $type;
        $storedfile->filename = $filename;
        $storedfile->location = $location;
        if (! $storedfile->save()) {
            Storage::delete($location);
            throw new \Exception("failed to save storedfile");
        }

        return $storedfile;
    }

    public function getMimeType()
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        if (isset($this->extension2mimetype[$extension])) {
            return $this->extension2mimetype[$extension];
        } else {
            return 'application/octet-stream';
        }
    }
}
