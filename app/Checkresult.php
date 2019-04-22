<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Checkresult extends Model
{

    protected $numberOfMessages = null;

    public function checkdetails()
    {
        return $this->hasMany('App\Checkdetail');
    }

    public function resultfile()
    {
        return $this->belongsTo('App\Storedfile');
    }

    public function delete()
    {
        // Checkresults are never deleted, only the associated files

        if ($this->resultfile !== null) {
            $this->resultfile->delete();
        }
    }

    public function detachFile()
    {
        $this->resultfile_id = null;
        return $this->save();
    }
    
    protected function computeNumberOfMessages()
    {
        if ($this->numberOfMessages === null) {
            $this->numberOfMessages = [];
            $levelTotals = Checkdetail::select(DB::raw('level, sum(number) as total'))->where('checkresult_id',
                $this->id)
                ->groupBy('level')
                ->orderBy('level', 'desc')
                ->get();
            foreach ($levelTotals as $levelTotal) {
                $this->numberOfMessages[$levelTotal['level']] = $levelTotal['total'];
            }
        }
    }

    public function getNumberOfMessages($level)
    {
        $this->computeNumberOfMessages();

        return ($this->numberOfMessages[$level] ?? 0);
    }

    public function getResult()
    {
        $this->computeNumberOfMessages();

        if (empty($this->numberOfMessages)) {
            $level = \ubfr\c5tools\CheckResult::CR_PASSED;
        } else {
            reset($this->numberOfMessages);
            $level = key($this->numberOfMessages);
        }
        $levelNames = \ubfr\c5tools\CheckResult::getLevelNames();

        return $levelNames[$level];
    }

    public function getNumberOfErrors()
    {
        return $this->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_FATAL) +
            $this->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_CRITICAL) +
            $this->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_ERROR);
    }

    public function getNumberOfWarnings()
    {
        return $this->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_WARNING);
    }

    public static function store($report, $filename, $result, $source, $userId, $checktime = 0.0, $checkmemory = 0)
    {
        if ($report !== null && ! ($report instanceof \ubfr\c5tools\Report)) {
            throw new \InvalidArgumentException("report invalid, expecting \ubfr\c5tools\Report");
        }
        if (! ($result instanceof \ubfr\c5tools\CheckResult)) {
            throw new \InvalidArgumentException("result invalid, expecting \ubfr\c5tools\CheckResult");
        }

        if ($report !== null) {
            $resultSpreadsheet = $report->getCheckResultAsSpreadsheet();
        } else {
            $resultSpreadsheet = $result->asSpreadsheet();
        }

        $tmpFilename = tempnam(sys_get_temp_dir(), 'c5fv');
        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($resultSpreadsheet);
        $xlsxWriter->save($tmpFilename);
        $resultFile = new \Illuminate\Http\File($tmpFilename);
        $resultFilename = pathinfo($filename, PATHINFO_FILENAME) . '-Validation-Result.xlsx';
        $storedResult = Storedfile::store($resultFile, $resultFilename, $source, Storedfile::TYPE_CHECK_RESULT, $userId);
        unlink($tmpFilename);

        DB::beginTransaction();
        try {
            $checkresult = new Checkresult();
            $checkresult->resultfile_id = $storedResult->id;
            $checkresult->sessionid = Session::getId();
            $checkresult->checktime = $checktime;
            $checkresult->checkmemory = $checkmemory;
            if (! $checkresult->save()) {
                throw new \Exception("failed to save Checkresult");
            }

            foreach ($result->asArray(1) as $detail) {
                $checkdetail = new Checkdetail();
                $checkdetail->checkresult_id = $checkresult->id;
                $checkdetail->level = $detail['level'];
                $checkdetail->number = $detail['number'];
                $checkdetail->message = $detail['message'];
                if (! $checkdetail->save()) {
                    throw new \Exception("failed to save Checkdetail");
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            try {
                $storedResult->delete();
            } catch (\Exception $de) {
                report($de);
            }
            throw $e;
        }

        return $checkresult;
    }
}
