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
        
        $input = $_POST['input'];
        
        if(empty($input)) {
            $out['text'] = 'Zadajte hladane slovo';
            echo json_encode($out);
            exit;
        }
        
        if(isset($_SESSION['keys']) && isset($_SESSION['keyCount']) && isset($_SESSION['words']) && isset($_SESSION['wordsCount'])) {
            $this->keys         = $_SESSION['keys'];
            $this->keyCount     = $_SESSION['keyCount'];
            $this->words        = $_SESSION['words'];
            $this->wordsCount   = $_SESSION['wordsCount'];
        } else {
            $this->loadDictionary();
        }
        
        if(isset($_SESSION['probability'])) {
            $this->probability = $_SESSION['probability'];
        } else {
            $this->createProbability();
        }
        
        if(isset($_SESSION['root'])) {
            $this->root = $_SESSION['root'];
        } else {
            $this->createTree();
        }
        
        $this->getCompareCount($input);
    }
    
    private function getCompareCount($word) {
        $cCount     = 0;
        $i          = 0;
        $j          = $this->keyCount;
        $continue   = true;
        $notFound   = false;
        
        while($continue && !$notFound) {
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
                $notFound = true;
            }
        }
        
        $out = [];
        $out['text'] = 'Pocet porovnani pre slovo "'.$word.'": '.$cCount;
        
        if($notFound) {
            $out['text'] .= ', ale slovo neexistuje v slovniku';
        }
        
        echo json_encode($out);
        exit;
    }
    
    private function createProbability() {
        foreach($this->keys AS $key => $value) {
            $this->probability[] = $value/$this->wordsCount;
        }
        $_SESSION['probability'] = $this->probability;
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
        
        $_SESSION['root'] = $this->root;
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
        
        $_SESSION['keys']       = $this->keys;
        $_SESSION['keyCount']   = $this->keyCount;
        $_SESSION['words']      = $this->words;
        $_SESSION['wordsCount'] = $this->wordsCount;
    }

}