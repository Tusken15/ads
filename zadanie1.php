<?php

$index = new Zadanie1();
$index->start();

class Zadanie1 {
    
    private $file;
    private $nodes;
    private $graph      = Array();
    private $result     = Array();
    private $maxColours = 0;
    private $domain     = 'https://patriksulak.000webhostapp.com/';
    
    public function start() {
        
        ini_set('display_errors', 0);
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 600);
        
        $this->file = $_FILES['file'];
        if(empty($this->file['name'])) {
            die('Nemožno otvoriť súbor'); 
        }
        
        $this->createGraph();
        $this->graphColour(0);
    }
    
    private function exportResult() {
        // create file
        $myfile = fopen("output.txt", "w") or die("Unable to open file!");
        $txt    = implode("\r\n", $this->result);
        fwrite($myfile, $txt);
        
        // download file
        $file_url = $this->domain.'output.txt';
        header('Content-Type: text/plain');
        header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
        readfile($file_url);
        exit;
    }
    
    private function graphColour($node) {
        for($color = 1; true; $color++) {
            
            if($color > $this->maxColours) {
                $this->maxColours = $color;
            }
            
            if($this->isSafe($node, $color)) {
                $this->result[$node] = $color;
                
                if(($node + 1) < $this->nodes) {
                    $this->graphColour($node + 1);
                } else {
                    $this->exportResult();
                    exit;
                }
            }
        }
    }
    
    private function isSafe($node, $color) {
        
        for($i = 0; $i < $this->nodes; $i++) {
            if(isset($this->result[$i]) && $i != $node && $this->graph[$node][$i] == 1 && $color == $this->result[$i]) {
                return false; 
            }
        }
        
        return true;
    }
    
    private function createGraph() {
        // load file
        $myfile         = fopen($this->file['tmp_name'], "r") or die("Unable to open file!");
        $graph          = explode(PHP_EOL, trim(fread($myfile,filesize($this->file['tmp_name']))));
        fclose($myfile);
        
        // node count
        $this->nodes    = trim($graph[0]);
        unset($graph[0]);
        $graph          = array_values($graph);
        $graphLength    = count($graph);
        
        // create graph
        for ($i = 0; $i < $graphLength; $i++) {
            // create values
            $values = explode(' ', trim($graph[$i]));
            
            for ($j = 0; $j < $graphLength; $j++) {
                $this->graph[$i][$j] = $values[$j];
            }
        }
    }

}