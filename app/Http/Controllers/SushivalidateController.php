<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Validatereportjr1Controller;
use App\Http\Controllers\Validatereportjr1goaController;
use App\Http\Controllers\Validatereportjr2Controller;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
Use Session;
Use Excel;
Use Mail;
use DateTime;
use App\Http\Manager\SubscriptionManager;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use Illuminate\Support\Facades\Storage;
use SoapClient;
use DOMDocument;
use XMLReader;


class SushivalidateController extends CommonController
{
    public $xmlFile;
    protected $xsdFile;
    public function setXMLFile($xmlFile)
    {
        if (!is_file($xmlFile)) {
            throw new \Exception(sprintf('XML file `%s` not found.', $xmlFile));
        }
        $this->xmlFile = $xmlFile;
        return $this;
    }
    public function setXSDFile($xsdFile)
    {
        if (!is_file($xsdFile)) {
            throw new \Exception(sprintf('XSD file `%s` not found.', $xsdFile));
        }
        $this->xsdFile = $xsdFile;
        return $this;
    }
    public function ValidateSushi()
    {
        $summary = array();
        libxml_use_internal_errors(true);
        if (!$this->xmlFile) {
            $summary[] = 'You must provide a XSD file with XSDValidator::setXSDFile.';
            //throw new \Exception('You must provide a XSD file with XSDValidator::setXSDFile.');
            
        }
        $reader = new \XMLReader();
        $reader->open($this->xmlFile);
        
        $reader->setParserProperty(XMLReader::VALIDATE, true);
        //validating xml
        if (!$reader->isValid()) {
            $errors = $this->getXMLErrorsString();
            $status = sprintf("Document `%s` is not valid :\n%s", $this->xmlFile, $errors);
             $summary[] = $status;
            //throw new \Exception(sprintf("Document `%s` is not valid :\n%s", $this->xmlFile, $errors));
             
        }
        //validating with xsd
        $xml = new \DOMDocument(); 
        $xml->load($this->xmlFile); 
        if (!$this->xsdFile) {
            $summary[] = 'You must provide a XSD file with XSDValidator::setXSDFile.';
            //throw new \Exception('You must provide a XSD file with XSDValidator::setXSDFile.');
             

        }
        if (!$xml->schemaValidate($this->xsdFile)) {
            $errors = $this->getXMLErrorsString();
            //$statusvalue = sprintf("Document `%s` does not validate XSD file :\n%s", $this->xmlFile, $errors);
            //$statusvalue =  $errors;
            $summary[] = $errors;
            //throw new \Exception(sprintf("Document `%s` does not validate XSD file :\n%s", $this->xmlFile, $errors));
            
        }

        //return $this;
        //echo "<pre>";print_r($summary);die;
        return $summary;
    }
     public function getXMLErrorsString()
    {
        $errorsString = '';
        $errors = libxml_get_errors();
        foreach ($errors as $key => $error) {
            $level = $error->level === LIBXML_ERR_WARNING? 'Warning' : $error->level === LIBXML_ERR_ERROR? 'Error' : 'Fatal';
            $errorsString .= sprintf("    [%s] %s", $level, $error->message);
            if($error->file) {
                $errorsString .= sprintf("    in %s (line %s, col %s)", $error->file, $error->line, $error->column);
            }
            $errorsString .= "\n";
        }
        return $errorsString;
    }
}