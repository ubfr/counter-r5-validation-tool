<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Reportfile extends Model
{

    public $timestamps = false;

    public function reportfile()
    {
        return $this->belongsTo('App\Storedfile');
    }

    public function checkresult()
    {
        return $this->belongsTo('App\Checkresult');
    }

    public function delete()
    {
        // Reportfiles are never deleted, only the associated files
        
        if ($this->reportfile !== null) {
            $this->reportfile->delete();
        }
        if($this->checkresult !== null) {
            $this->checkresult->delete();
        }
    }
    
    public function detachFile()
    {
        $this->reportfile_id = null;
        return $this->save();
    }
    
    public static function store($report, $file, $filename, $source, $result, $userId)
    {
        if ($report !== null && ! ($report instanceof \ubfr\c5tools\Report)) {
            throw new \InvalidArgumentException("report invalid, expecting \ubfr\c5tools\Report");
        }

        $storedReport = null;
        $storedResult = null;
        try {
            $storedReport = Storedfile::store($file, $filename, $source, Storedfile::TYPE_REPORT, $userId);
            $storedResult = Checkresult::store($report, $filename, $result, $source, $userId);

            $reportfile = new Reportfile();
            $reportfile->reportfile_id = $storedReport->id;
            $reportfile->checkresult_id = $storedResult->id;
            if ($report !== null) {
                $reportfile->release = $report->getRelease();
                $reportfile->reportname = $report->getReportName();
                $reportfile->reportid = $report->getReportId();
                $reportFilters = $report->getReportFilters();
                if (isset($reportFilters['Platform'])) {
                    $reportfile->platform = $reportFilters['Platform'];
                }
                $reportfile->institutionname = $report->getInstitutionName();
                $created = $report->getCreated();
                if ($created !== null) {
                    $reportfile->created = substr($created, 0, 10) . ' ' . substr($created, 11, 8);
                }
                $reportfile->createdby = $report->getCreatedBy();
                $reportfile->begindate = $report->getBeginDate();
                $reportfile->enddate = $report->getEndDate();
            }
            if (! $reportfile->save()) {
                throw new \Exception("failed to save Reportfile");
            }
        } catch (\Exception $e) {
            try {
                if ($storedReport !== null) {
                    $storedReport->delete();
                }
                if ($storedResult !== null) {
                    $storedResult->deleteResultfile();
                }
            } catch (\Exception $de) {
                report($de);
            }
            throw $e;
        }

        return $reportfile;
    }
}
