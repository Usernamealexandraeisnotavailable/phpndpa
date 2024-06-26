<?php
class wff {
	
	public function replace(wff $p, wff $q) {
		$array = $this->construction;
		foreach ($array as $index => $a) {
			if ($a == str_replace(["<span id='36'>","</span>"],"",strval($p)))
				$this->construction[$index] = str_replace(["<span id='36'>","</span>"],"",strval($q));
			if ($a instanceof wff) {
				$a->replace($p,$q);
				$this->construction[$index] = $a;
			}
			$this->expression = str_replace($p,$q,$this->expression);
		}
		return $this;
	}
	
	public function __toString() : string { return $this->expression; }
	public function __construct (string $l = "p") {
		for ($i = 0; $i < strlen($l); $i++) {
			if (!in_array(strtolower($l[$i]),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','-','_','.'])) {
				throw new InvalidArgumentException("For variable names, only use alphanumeric symbols, -, ., and _.");
			}
		}
		$this->expression = "<span id='36'>$l</span>";
		$this->construction = [$l];
	}
	public static function predicate (string $a, wff $b) {
		for ($i = 0; $i < strlen($a); $i++) {
			if (!in_array(strtolower($a[$i]),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','-','_','.'])) {
				throw new InvalidArgumentException("For predicate names, only use alphanumeric symbols, -, ., and _.");
			}
		}
		$prop = new wff($a);
		$prop->expression = "$a($b)";
		$prop->construction = [$a,$b->getConstruction()];
		return $prop;
	}
	public function turnIntoConstant () : void {
		$this->construction = [str_replace(["<span id='36'>","</span>"],"",$this->expression)];
		$this->expression = str_replace("<span id='36'>","<span id='35'>",$this->expression);
	}
	public function getConstruction () : array { return $this->construction; }
	
	
	private function andL (wff $b) : void {
		$a = new wff;
		$a = clone $this;
		$this->expression = "(".$this->expression." ∧ $b)";
		$this->construction = ["and",$a,$b];
	}
	private function orL (wff $b) : void {
		$a = new wff;
		$a = clone $this;
		$this->expression = "(".$this->expression." ∨ $b)";
		$this->construction = ["or",$a,$b];
	}
	private function impliesL (wff $b) : void {
		$a = new wff;
		$a = clone $this;
		$this->expression = "(".$this->expression." → $b)";
		$this->construction = ["implies",$a,$b];
	}
	private function notI () : void {
		$a = new wff;
		$a = clone $this;
		$this->expression = "¬".$this->expression;
		$this->construction = ["not",$a];
	}
	private function forallI (string $a) : void {
		for ($i = 0; $i < strlen($a); $i++) {
			if (!in_array(strtolower($a[$i]),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','-','_','.'])) {
				throw new InvalidArgumentException("For variable names, only use alphanumeric symbols, -, ., and _.");
			}
		}
		$b = new wff;
		$b = clone $this;
		$this->expression = "(∀$a ".$this->expression.")";
		$this->construction = ["forall",$a,$b];
	}
	private function existsI (string $a) : void {
		for ($i = 0; $i < strlen($a); $i++) {
			if (!in_array(strtolower($a[$i]),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','-','_','.'])) {
				throw new InvalidArgumentException("For variable names, only use alphanumeric symbols, -, ., and _.");
			}
		}
		$b = new wff;
		$b = clone $this;
		$this->expression = "(∃$a ".$this->expression.")";
		$this->construction = ["exists",$a,$b];
	}
	
	public static function and (wff $a, wff $b) : wff {
		$p = new wff; $q = new wff;
		$p = clone $a; $q = clone $b;
		$p->andL($q);
		return $p;
	}
	public static function or (wff $a, wff $b) : wff {
		$p = new wff; $q = new wff;
		$p = clone $a; $q = clone $b;
		$p->orL($q);
		return $p;
	}
	public static function implies (wff $a, wff $b) : wff {
		$p = new wff; $q = new wff;
		$p = clone $a; $q = clone $b;
		$p->impliesL($q);
		return $p;
	}
	public static function not (wff $a) : wff {
		$p = new wff;
		$p = clone $a;
		$p->notI();
		return $p;
	}
	public static function forall (string $a, wff $b) : wff {
		$p = new wff;
		$p = clone $b;
		$p->forallI($a);
		return $p;
	}
	public static function exists (string $a, wff $b) : wff {
		$p = new wff;
		$p = clone $b;
		$p->existsI($a);
		return $p;
	}
	
	
	protected string $expression;
	protected array $construction;
	
};
class sequent {
	
	public function __toString() : string {
		$return = "{";
		foreach ($this->premises as $i => $premise) {
			if ($i > 0) $return .= ", ";
			$return .= "$premise";
		}
		$return .= "} ⊢ ".strval($this->conclusion);
		return $return;
	}
    public function __construct (array $premises, wff $conclusion) {
		if ($premises != []) {
			foreach ($premises as $premise) {
				if (!$premise instanceof wff) {
					throw new InvalidArgumentException("All premises must be wff.");
				}
			}
        }
        $this->premises = $premises;
		$this->conclusion = $conclusion;
    }
	public function getPremises () : array { return $this->premises; }
	public function getConclusion () : wff { return $this->conclusion; }
	
	protected array $premises;
	protected wff $conclusion;
	
	
};
class application {
	
	public function __toString() : string { return $this->application; }
	public function __construct (sequent $application, string $name) {
		$this->isSorry = false;
		$this->application = $application;
		$this->proof = [];
		$this->name = $name;
		if ($application->getPremises() != []) {
			foreach ($application->getPremises() as $premise) {
				$this->proof[] = array("sequent"=>new sequent($application->getPremises(),$premise),"inference"=>"R","args"=>"");
			}
		}
	}
	public function print() : void {
		$str = "<span id='34'>╒══<span id='35'> Proof </span>for ";
		$str .= "<span id='34'></span>";
		$max = 0;
		$test = "";
		foreach ($this->proof as $prop) {
			$word = str_replace(["<span id='32'>","<span id='31'>","<span id='30'>","<span id='34'>","<span id='36'>","<span id='37'>","<span id='38'>","</span>","<span id='35'>"],"",str_replace(["∧","∨","→","¬","∀","∃"],"#",$prop["sequent"]));
			if (strlen($word) > $max) {
				$max = max($max,strlen($word));
				$test = $word;
			}
		}
		$displayArg = [];
		foreach ($this->proof as $index => $prop) {
			$counter = strlen(str_replace(["<span id='32'>","<span id='31'>","<span id='30'>","<span id='34'>","<span id='36'>","<span id='37'>","<span id='38'>","</span>","<span id='35'>"],"",str_replace(["∧","∨","→","¬","∀","∃"],"#",$prop["sequent"])));
			$displayArg[$index] = "";
			for ($i = 0; $i <= $max-$counter; $i++) {
				$displayArg[$index] .= " ";
			}
		}
		$str .= "</span>";
		$str .= $this->application;
		$str .= " <span id='34'>:</span>\n";
		$j = strlen(strval(array_key_last($this->proof)));
		foreach ($this->proof as $index => $prop) {
			$ds[$index] = "";
			for ($i = 0; $i < $j-strlen(strval($index)); $i++) {
				$ds[$index] .= " ";
			}
		}
		foreach ($this->proof as $i => $step) {
			if ($i != array_key_last($this->proof)) $str .= "<span id='34'>│  </span>";
			else $str .= "<span id='34'>└──</span>";
			if (strval($step["sequent"]) == strval($this->application))
				$str .= " [<span id='34'>∎</span>]".$ds[1]."</span> ".$step["sequent"]."";
			else
				$str .= " [<span id='32'>$i</span>]".$ds[$i]."<span id='31'> ".str_replace(["<span id='36'>","</span>"],["<span id='33'>","<span id='31'>"],$step["sequent"])."";
			$str .= "<span id='35'>".$displayArg[$i].$step["inference"]."</span>(<span id='32'>".str_replace(",","</span>,<span id='32'>",$step["args"])."</span>)\n";
		}
		print $str."</span>";
	}
	
	
	private function combinePremises (array $a) : array {
		$c = [];
		foreach ($a as $seq) {
			$c = array_merge($c,$seq->getPremises());
		}
		return array_unique($c);
	}
	
	
	public function conjunctionIntroduction (int $m, int $n) {
		$_proof = $this->proof;
		$pm = clone ($_proof[$m])["sequent"]->getConclusion();
		$pn = clone ($_proof[$n])["sequent"]->getConclusion();
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises (
					[($_proof[$m])["sequent"],
					($_proof[$n])["sequent"]]
				),
				wff::and($pm,$pn)
			),
			"inference"=>"∧I",
			"args"=>"$m,$n"
		);
		return $this;
	}
	public function conjunctionEliminationLeft (int $m) {
		$_proof = $this->proof;
		$pm = clone ($_proof[$m])["sequent"]->getConclusion();
		if ($pm->getConstruction()[0] != "and") {
			throw new InvalidArgumentException("No conjunction found.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				($_proof[$m])["sequent"]->getPremises(),
				$pm->getConstruction()[1]
			),
			"inference"=>"∧E",
			"args"=>"$m"
		);
		return $this;
	}
	public function conjunctionEliminationRight (int $m) {
		$_proof = $this->proof;
		$pm = clone ($_proof[$m])["sequent"]->getConclusion();
		if ($pm->getConstruction()[0] != "and") {
			throw new InvalidArgumentException("No conjunction found.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				($_proof[$m])["sequent"]->getPremises(),
				$pm->getConstruction()[2]
			),
			"inference"=>"∧E",
			"args"=>"$m"
		);
		return $this;
	}
	public function disjunctionIntroductionLeft (int $n, wff $q) {
		$_proof = $this->proof;
		$p = ($_proof[$n])["sequent"]->getConclusion();
		$this->proof[] =
		array("sequent"=>new sequent (
				($_proof[$n])["sequent"]->getPremises(),
				wff::or($p,$q)
			),
			"inference"=>"∨I",
			"args"=>"$n"
		);
		return $this;
	}
	public function disjunctionIntroductionRight (int $n, wff $q) {
		$_proof = $this->proof;
		$p = ($_proof[$n])["sequent"]->getConclusion();
		$this->proof[] =
		array("sequent"=>new sequent (
				($_proof[$n])["sequent"]->getPremises(),
				wff::or($q,$p)
			),
			"inference"=>"∨I",
			"args"=>"$n"
		);
		return $this;
	}
	public function startSubproof (wff $a) {
		$_proof = $this->proof;
		if ($_proof != [])
			$premises = ($_proof[array_key_last($_proof)])["sequent"]->getPremises();
		else $premises = [];
		$this->proof[] =
		array("sequent"=>new sequent (
				array_merge($premises,[$a]),
				$a
			),
			"inference"=>"R",
			"args"=>""
		);
		return $this;
	}
	public function conditionalIntroduction (int $i) {
		$_proof = $this->proof;
		$prem = ($_proof[$i])["sequent"]->getPremises();
		$premises = [];
		foreach ($prem as $index => $pr) {
			if ($index != array_key_last($prem))
				$premises[] = $pr;
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				$premises,
				wff::implies(
					$prem[array_key_last($prem)],
					($_proof[$i])["sequent"]->getConclusion()
				)
			),
			"inference"=>"→I",
			"args"=>"$i"
		);
		return $this;
	}
	public function conditionalElimination (int $m, int $n) {
		$_proof = $this->proof;
		$pm = clone ($_proof[$m])["sequent"]->getConclusion();
		if ($pm->getConstruction()[0] != "implies") {
			throw new InvalidArgumentException("No conditional found.");
		}
		if (strval($pm->getConstruction()[1]) != strval(($_proof[$n])["sequent"]->getConclusion())) {
			throw new InvalidArgumentException("Did you prove the antecedent yet ?");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises(
					[($_proof[$m])["sequent"],
					($_proof[$n])["sequent"]]
				),
				$pm->getConstruction()[2]
			),
			"inference"=>"→E",
			"args"=>"$m,$n"
		);
		return $this;
	}
	public function disjunctionElimination (int $a, int $b, int $c) {
		$_proof = $this->proof;
		$pa = clone ($_proof[$a])["sequent"]->getConclusion();
		$pb = clone ($_proof[$b])["sequent"]->getConclusion();
		$pc = clone ($_proof[$c])["sequent"]->getConclusion();
		if ($pa->getConstruction()[0] != "or") {
			throw new InvalidArgumentException("No disjunction found.");
		}
		if ($pb->getConstruction()[0] != "implies") {
			throw new InvalidArgumentException("No first conditional found.");
		}
		if ($pc->getConstruction()[0] != "implies") {
			throw new InvalidArgumentException("No second conditional found.");
		}
		if (strval($pb->getConstruction()[2]) != strval($pc->getConstruction()[2])) {
			throw new InvalidArgumentException("Incompatible consequents.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises(
					[($_proof[$a])["sequent"],
					($_proof[$b])["sequent"],
					($_proof[$c])["sequent"]]
				),
				$pb->getConstruction()[2]
			),
			"inference"=>"∨E",
			"args"=>"$a,$b,$c"
		);
		return $this;
	}
	public function negationIntroduction (int $a, int $b) {
		$_proof = $this->proof;
		$pa = clone ($_proof[$a])["sequent"]->getConclusion();
		$pb = clone ($_proof[$b])["sequent"]->getConclusion();
		if ($pa->getConstruction()[0] != "implies") {
			throw new InvalidArgumentException("No first conditional found.");
		}
		if ($pb->getConstruction()[0] != "implies") {
			throw new InvalidArgumentException("No second conditional found.");
		}
		if (strval($pa->getConstruction()[2]) != strval(wff::not($pb->getConstruction()[2]))) {
			throw new InvalidArgumentException("Incompatible consequents.");
		}
		if (strval($pa->getConstruction()[1]) != strval($pb->getConstruction()[1])) {
			throw new InvalidArgumentException("Incompatible antecedents.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises(
					[($_proof[$a])["sequent"],
					($_proof[$b])["sequent"]]
				),
				wff::not($pa->getConstruction()[1])
			),
			"inference"=>"¬I",
			"args"=>"$a,$b"
		);
		return $this;
	}
	public function negationElimination (int $a, int $b, wff $c) {
		$_proof = $this->proof;
		$pa = clone ($_proof[$a])["sequent"]->getConclusion();
		$pb = clone ($_proof[$b])["sequent"]->getConclusion();
		if (strval($pa) != strval(wff::not($pb))) {
			throw new InvalidArgumentException("The first argument is not the negation of the second one.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises(
					[($_proof[$a])["sequent"],
					($_proof[$b])["sequent"]]
				),
				$c
			),
			"inference"=>"¬E",
			"args"=>"$a,$b"
		);
		return $this;
	}
	public function existentialElimination (string $a, int $b) {
		$_proof = $this->proof;
		$p = clone ($_proof[$b])["sequent"]->getConclusion();
		$_a = new wff($a);
		if ($p->getConstruction()[0] != "exists") {
			throw new InvalidArgumentException("No existential quantifier found.");
		}
		foreach ($_proof as $prop) {
			$q = clone $prop["sequent"]->getConclusion();
			if ((substr_count("$q","<span id='36'>$a</span>") > 0 or substr_count("$q","<span id='35'>$a</span>") > 0)
				and !(substr_count("$q","∃$a") == 0 or substr_count("$q","∀$a") == 0))
				throw new InvalidArgumentException("Use another variable name.");
		}
		$_0 = new wff(strval($p->getConstruction()[1]));
		$_a->turnIntoConstant();
		$conclusion = $p->getConstruction()[2];
		$conclusion->replace($_0,$_a);
		$this->proof[] =
		array("sequent"=>new sequent (
				($this->proof[$b])["sequent"]->getPremises(),
				$conclusion
			),
			"inference"=>"∃E",
			"args"=>"$b"
		);
		return $this;
	}
	public function existentialIntroduction (wff $a, string $b, int $c) {
		$_proof = $this->proof;
		$p = clone ($_proof[$c])["sequent"]->getConclusion();
		foreach ($_proof as $prop) {
			$q = clone $prop["sequent"]->getConclusion();
			if ((substr_count("$q","<span id='36'>$b</span>") > 0 or substr_count("$q","<span id='35'>$b</span>") > 0)
				and !(substr_count("$q","∃$a") == 0 or substr_count("$q","∀$a") == 0))
				throw new InvalidArgumentException("Use another variable name.");
		}
		$_b = new wff($b);
		$p->replace($a,$_b);
		$this->proof[] =
		array("sequent"=>new sequent (
				($this->proof[$c])["sequent"]->getPremises(),
				wff::exists($b,$p)
			),
			"inference"=>"∃I",
			"args"=>"$c"
		);
		return $this;
	}
	public function universalElimination (string $a, int $b) {
		$_proof = $this->proof;
		$p = clone ($_proof[$b])["sequent"]->getConclusion();
		$_a = new wff($a);
		if ($p->getConstruction()[0] != "forall") {
			throw new InvalidArgumentException("No universal quantifier found.");
		}
		foreach ($_proof as $prop) {
			$q = clone $prop["sequent"]->getConclusion();
			if ((substr_count("$q","<span id='36'>$a</span>") > 0 or substr_count("$q","<span id='35'>$a</span>") > 0)
				and !(substr_count("$q","∃$a") == 0 or substr_count("$q","∀$a") == 0))
				throw new InvalidArgumentException("Use another variable name.");
		}
		$_0 = new wff(strval($p->getConstruction()[1]));
		$conclusion = $p->getConstruction()[2];
		$conclusion->replace($_0,$_a);
		$this->proof[] =
		array("sequent"=>new sequent (
				($this->proof[$b])["sequent"]->getPremises(),
				$conclusion
			),
			"inference"=>"∀E",
			"args"=>"$b"
		);
		return $this;
	}
	public function universalIntroduction (string $a, string $b, int $c) {
		$_proof = $this->proof;
		$_a = new wff($a);
		$p = clone ($_proof[$c])["sequent"]->getConclusion();
		$_b = new wff($b);
		foreach ($_proof as $prop) {
			$q = clone $prop["sequent"]->getConclusion();
			if ((substr_count("$q","<span id='36'>$b</span>") > 0 or substr_count("$q","<span id='35'>$b</span>") > 0)
				and !(substr_count("$q","∃$a") == 0 or substr_count("$q","∀$a") == 0))
				throw new InvalidArgumentException("Use another variable name.");
		}
		$p->replace($_a,$_b);
		if (substr_count(strval($p),"<span id='35'>$a</span>") > 0) {
			throw new InvalidArgumentException("Attempted universal generalization over a constant value.");
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				($this->proof[$c])["sequent"]->getPremises(),
				wff::forall($b,$p)
			),
			"inference"=>"∀I",
			"args"=>"$c"
		);
		return $this;
	}
	
	public function eval(application $application, array $lines) {
		if (count($lines) != count($application->getTheorem()->getPremises())) {
			throw new InvalidArgumentException("Mismatched line count <-> premises count in application evaluation.");
		}
		$args = "";
		$premises = [];
		foreach ($lines as $index => $ref) {
			if (!in_array(($this->proof[$ref])["sequent"]->getConclusion(),$application->getTheorem()->getPremises())) {
				throw new InvalidArgumentException("Premise not found.");
			}
			if ($index > 0)
				$args .= ",";
			$args .= $ref;
			$premises[] = ($this->proof[$ref])["sequent"];
		}
		$this->proof[] =
		array("sequent"=>new sequent (
				application::combinePremises(
					$premises
				),
				($application->getTheorem())->getConclusion()
			),
			"inference"=>$application->name,
			"args"=>$args
		);
		if ($application->isSorry()) {
			$this->name = "<span id='31'>".$this->name;
			$this->isSorry = true;
		}
		return $this;
	}
	
	public function sorry() {
		$this->proof[] =
		array("sequent"=>new sequent (
				[],
				new wff("Sorry")
			),
			"inference"=>"#",
			"args"=>""
		);
		$this->name = "<span id='31'>".$this->name;
		$this->isSorry = true;
		return $this;
	}
	
	public function getTheorem () { return $this->application; }
	public function getProof () { return $this->proof; }
	public function isSorry () { return $this->isSorry; }
	
	protected sequent $application;
	protected array $proof;
	protected string $name;
	protected bool $isSorry;
	
};
?>
