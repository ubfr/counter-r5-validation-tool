<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
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

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function checkresult()
    {
        return $this->hasOne('App\Checkresult', 'resultfile_id');
    }
    
    public function reportfile()
    {
        return $this->hasOne('App\Reportfile', 'reportfile_id');
    }
    
    public function sushiresponse()
    {
        return $this->hasOne('App\Sushiresponse', 'responsefile_id');
    }

    protected function __construct() {
        return parent::__construct();
    }
    
    public function delete()
    {
        Storage::delete($this->location);

        return parent::delete();
    }

    public function exists()
    {
        return Storage::exists($this->location);
    }
    
    public function getSourceName()
    {
        return self::$sourceNames[$this->source];
    }
    
    public static function store($file, $filename, $source, $type, $userId)
    {
        if (! ($file instanceof \Illuminate\Http\File) && ! ($file instanceof \Illuminate\Http\UploadedFile)) {
            throw new \InvalidArgumentException("file invalid, expecting File or UploadedFile");
        }
        if (! isset(self::$sourceNames[$source])) {
            throw new \InvalidArgumentException("source {$source} not within the allowed range");
        }
        if (! isset(self::$typeNames[$type])) {
            throw new \InvalidArgumentException("type {$type} not within the allowed range");
        }
        $user = User::where('id', $userId)->firstOrFail();
        
        $location = $file->hashName('storedfiles');
        if (Storage::put($location, Crypt::encrypt(bzcompress(File::get($file->path()), 9), false)) === false) {
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

    public function getTemporaryFile()
    {
        $tmpFilename = tempnam(sys_get_temp_dir(), 'c5fv');
        File::put($tmpFilename, bzdecompress(Crypt::decrypt(Storage::get($this->location), false)));
        $file = new \Illuminate\Http\File($tmpFilename);
        
        return $file;
    }
    
    public function download()
    {
        $tmpFile = $this->getTemporaryFile();
        
        return response()->download($tmpFile->path(), $this->filename, [
            'Content-Type: ' . $this->getMimeType()
        ]);
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
