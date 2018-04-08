<?php

$index = new Zadanie2();
$index->start();

class Zadanie2 {
    
    private $file;
    private $maxWeight;
    private $itemsCount     = 0;
    private $maxFragile;
    private $knapsack;
    private $items          = Array();
    private $weights        = Array();
    private $fragile        = Array();
    private $name           = Array();
    private $selected       = Array();
    private $maxValue;
    private $domain         = 'http://patriksulak.6f.sk/';
    
    public function start() {
        
        ini_set('display_errors',       1);
        ini_set('memory_limit',         '1024MB');
        ini_set('max_execution_time',   600);
        
        $this->file = $_FILES['file2'];
        if(empty($this->file['name'])) {
            header('Location: '.$this->domain);
            exit;
        }

        $this->loadFile();
        $this->getKnapsack();
        $this->getSelected();
        $this->exportResult();
    }
    
    private function exportResult() {
        // create file
        $myfile         = fopen("out.txt", "w") or die("Unable to open file!");
        asort($this->selected);
        $txt            = $this->maxValue."\r\n".count($this->selected)."\r\n".implode("\r\n", $this->selected);
        fwrite($myfile, $txt);
        
        // download file
        $file_url = $this->domain.'out.txt';
        header('Content-Type: text/plain');
        header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
        readfile($file_url);
        exit;
    }
    
    private function getMax($a, $b) { 
        return ($a > $b)? $a : $b; 
    }
 
    private function getKnapsack() {
        for ($i = 0; $i <= $this->itemsCount; $i++) {
            // create virtual table
            for ($w = 0; $w <= $this->maxWeight; $w++) {
                for($f = 0; $f <= $this->maxFragile; $f++) {
                    if ($i == 0 || $w == 0) {
                        $this->knapsack[$i][$w][$f] = 0;
                    } else if ($this->weights[$i-1] > $w || ($f == 0 && $this->fragile[$i-1])) {
                        $this->knapsack[$i][$w][$f] = $this->knapsack[$i-1][$w][$f];
                    } else {
                        if($this->fragile[$i-1]) {
                            $this->knapsack[$i][$w][$f] = $this->getMax( $this->items[$i-1] + 
                                                $this->knapsack[$i-1][$w-$this->weights[$i-1]][$f-1],  
                                                $this->knapsack[$i-1][$w][$f]);
                        } else {
                            $this->knapsack[$i][$w][$f] = $this->getMax( $this->items[$i-1] + 
                                                $this->knapsack[$i-1][$w-$this->weights[$i-1]][$f],  
                                                $this->knapsack[$i-1][$w][$f]);
                        }
                    }
                } 
            } 
        }
        $this->maxValue = $this->knapsack[$i-1][$w-1][$f-1];
    }
     
    private function getSelected() {
         
        for($i = $this->itemsCount, $w = $this->maxWeight, $f = $this->maxFragile; $i > 0 ;$i--) {
            if($this->knapsack[$i][$w][$f] != $this->knapsack[$i - 1][$w][$f]) {
                $this->selected[] = $this->name[$i-1];
                $w -= $this->weights[$i-1];
                if($this->fragile[$i-1]) {
                    $f--;
                }
            }
        }
    }
 
    private function loadFile() {
        // load file
        $myfile = fopen($this->file['tmp_name'], "r") or die("Unable to open file!");
        $file   = explode(PHP_EOL, trim(fread($myfile,filesize($this->file['tmp_name']))));
        fclose($myfile);
        
        // set variables
//        $this->itemsCount   = (int)trim($file[0]);
        $this->maxWeight    = (int)trim($file[1]);
        $this->maxFragile   = (int)trim($file[2]);
        
        unset($file[0], $file[1], $file[2]);
        
        $file       = array_values($file);
        $fileLength = count($file);
        
        // create items
        for ($i = 0; $i < $fileLength; $i++) {
            // create values
            $values             = explode(' ', trim($file[$i]));
            
            if((int)$values[2] > $this->maxWeight) {
                continue;
            }
            $this->itemsCount++;
            
            $this->name[]       = trim($values[0]);
            $this->items[]      = (int)$values[1];
            $this->weights[]    = (int)$values[2];
            $this->fragile[]    = (int)$values[3];
        }
        array_multisort($this->weights, $this->items, $this->fragile, $this->name);
    }

}