<?php

$index = new Zadanie3();
$index->start();

class Zadanie3 {
    protected $words        = [];
    protected $keys         = [];
    protected $probability  = [];
    protected $root         = [];
    protected $wordsCount   = 0;
    protected $keyCount     = 0;
    private $domain         = 'http://patriksulak.6f.sk/';
    
    public function start() {
        
        ini_set('display_errors',       1);
        ini_set('memory_limit',         '1024MB');
        ini_set('max_execution_time',   600);
        
        $this->word = $_POST['input3'];
        
        $this->loadDictionary();
        $this->createProbability();
        $this->createTree();
        $this->getCompareCount('might');
    }
    
    private function getCompareCount($word) {
        $cCount     = 0;
        $i          = 0;
        $j          = $this->keyCount;
        $continue   = true;
        
        while($continue) {
            $pos    = $this->root[$i][$j];
            $curr   = key(array_slice($this->keys, $pos-1, 1));
            $cCount++;
            
            if($word == $curr) {
                $continue = false;
            } elseif($word < $curr) {
                $j = $pos - 1;
            } elseif($word > $curr) {
                $i = $pos;
            }
            
            if($i == $j) {
                $cCount++;
                $continue = false;
                echo 'neexistuje';
                exit;
            }
            echo $i.' '.$j.'<br>';  
        }
        
        echo 'Pocet porovnani: '.$cCount;
    }
    
    private function createProbability() {
        foreach($this->keys AS $key => $value) {
            $this->probability[] = $value/$this->wordsCount;
        }
    }
    
    private function createTree() {
        
        $t = 0;
        $q = [];
        
        foreach($this->words AS $word => $count) {
            if(isset($this->keys[$word])) {
                $q[]    = $t;
                $t      = 0;
            } else {
                $t     += $count/$this->wordsCount;
            }
        }
 
        $q[]    = $t;
        $e      = [];
        $w      = [];
        $n      = $this->keyCount;
        
        for($i = 0; $i <= $n; $i++) {
            $e[$i][$i] = $q[$i];
            $w[$i][$i] = $q[$i];
        }
        
        for($l = 1; $l <= $n; $l++) {
            for($i = 0; $i <= $n-$l; $i++) {
                $j = $i+$l;
                $w[$i][$j] = $w[$i][$j-1] + $this->probability[$j-1] + $q[$j];
                for($r = $i+1; $r <= $j; $r++) {
                    $t = $e[$i][$r-1] + $e[$r][$j] + $w[$i][$j];
                    if(!isset($e[$i][$j]) || $t < $e[$i][$j]) {
                        $e[$i][$j]          = $t;
                        $this->root[$i][$j] = $r;
                    }
                }
            }
        }
    }
     
    private function loadDictionary() {
        $myfile     = fopen('data/dictionary.txt', "r") or die("Unable to open file!");
        $file       = explode(PHP_EOL, trim(fread($myfile,filesize('data/dictionary.txt'))));
        $fileLength = count($file);
        fclose($myfile);
        
        for ($i = 0; $i < $fileLength; $i++) {
            $values                  = explode(' ', trim($file[$i]));
            $this->wordsCount       += $values[0];
            $this->words[$values[1]] = $values[0];  
        
            if($values[0] > 50000) {
                $this->keys[$values[1]] = $values[0];
            }
        }
        
        $this->keyCount = count($this->keys);
        
        ksort($this->words);
        ksort($this->keys);
    }

}