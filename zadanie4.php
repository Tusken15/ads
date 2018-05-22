<?php

$index = new Zadanie4();
$index->start();

class Zadanie4 {
    
    private $file;
    private $adj            = [];
    private $adjInv         = [];
    private $visited        = [];
    private $visitedInv     = [];
    private $stack          = [];
    private $scc            = [];
    private $counter        = 1;
    private $nbvar;
    private $nbclauses;
    private $domain         = 'http://patriksulak.6f.sk/';
    
    public function start() {
        
        ini_set('display_errors',       1); 
        ini_set('memory_limit',         '1024MB');
        ini_set('max_execution_time',   600);
        
        $this->file = $_FILES['file'];
        if(empty($this->file['name'])) {
            header('Location: '.$this->domain);
            exit;
        }

        $this->loadFile();
        $out = $this->is2Satisfiable();
        
        echo json_encode(['out' => implode('<br>', $out)]);
        exit;
    }
    
    private function is2Satisfiable() {
        // STEP 1 of Kosaraju's Algorithm which
        // traverses the original graph
        for($i = 1; $i <= 2 * $this->nbvar; $i++) {
            if (!$this->visited[$i]) {
                $this->dfsFirst($i);
            }
        }

        // STEP 2 pf Kosaraju's Algorithm which
        // traverses the inverse graph. After this,
        // array scc[] stores the corresponding value
        while (count($this->stack) > 0) {
            $lastIndex = count($this->stack) - 1;
            $k = $this->stack[$lastIndex];
            unset($this->stack[$lastIndex]);

            if(!$this->visitedInv[$k]) {
                $this->dfsSecond($k);
                $this->counter++;
            }
        }

        for ($i = 1; $i <= $this->nbvar; $i++) {
            // for any 2 vairable x and -x lie in
            // same SCC
            if ($this->scc[$i] == $this->scc[$i + $this->nbvar]) {
                $out[] = "NESPLNITELNA";
                return $out;
            }
        }

        // no such variables x and -x exist which lie
        // in same SCC
        $out[] = "SPLNITELNA";

        for($i = 1; $i <= $this->nbvar; $i++) {
            if($this->scc[$i] > $this->scc[$i + $this->nbvar]) {
                $out[] = "PRAVDA";
            } else {
                $out[] = "NEPRAVDA";
            }
        }
        
        return $out;
    }
    
    private function dfsFirst($u) {
        if($this->visited[$u]) {
            return;
        }
        
        $this->visited[$u]  = true;
        $cnt                = count($this->adj[$u]);
        
        for($i = 0; $i < $cnt; $i++) {
            $this->dfsFirst($this->adj[$u][$i]);
        }
        
        $this->stack[] = $u;
    }
    
    private function dfsSecond($u) {
        if($this->visitedInv[$u]) {
            return;
        }
        
        $this->visitedInv[$u]   = true;
        $cnt                    = count($this->adjInv[$u]);
        
        for($i = 0; $i < $cnt; $i++) {
            $this->dfsSecond($this->adjInv[$u][$i]);
        }
        
        $this->scc[$u] = $this->counter;
    }
    
    private function addEdges($a, $b) {
        $this->adj[$a][] = $b;
    }
    
    private function addEdgesInverse($a, $b) {
        $this->adjInv[$b][] = $a;
    }
    
    private function createEdge($a, $b) {
        
        $n = $this->nbvar;
        
        if($a > 0 && $b > 0) {
            $this->addEdges($a + $n, $b);
            $this->addEdgesInverse($a + $n, $b);
            $this->addEdges($b + $n, $a);
            $this->addEdgesInverse($b + $n, $a);
        } elseif($a > 0 && $b < 0) {
            $this->addEdges($a + $n, $n - $b);
            $this->addEdgesInverse($a + $n, $n - $b);
            $this->addEdges(-$b, $a);
            $this->addEdgesInverse(-$b, $a);
        } elseif ($a < 0 && $b > 0) {
            $this->addEdges(-$a, $b);
            $this->addEdgesInverse(-$a, $b);
            $this->addEdges($b + $n, $n - $a);
            $this->addEdgesInverse($b + $n, $n - $a);
        } else {
            $this->addEdges(-$a, $n - $b);
            $this->addEdgesInverse(-$a, $n - $b);
            $this->addEdges(-$b, $n - $a);
            $this->addEdgesInverse(-$b, $n - $a);
        }
    }
    
    private function loadFile() {
        $myfile = fopen($this->file['tmp_name'], "r") or die("Unable to open file!");
        $file   = explode(PHP_EOL, trim(fread($myfile,filesize($this->file['tmp_name']))));
        fclose($myfile);
        
        $firstLine          = explode(' ', trim($file[0]));
        $this->nbvar        = (int)trim($firstLine[0]);
        $this->nbclauses    = (int)trim($firstLine[1]);
        $fileLength         = count($file);
        
        // create items
        for ($i = 1; $i < $fileLength; $i++) {
            // create values
            $values = explode(' ', trim($file[$i]));
            $a      = $values[0];
            $b      = $values[1];
            if($b == 0) {
                $b = $a;
            }
            
            $this->createEdge($a, $b);
        }
    }

}