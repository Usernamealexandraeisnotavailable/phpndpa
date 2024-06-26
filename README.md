# PHPNDPA : PHP Natural Deduction Proof Assistant
## A proof assistant for PHP
PHP is the language i use the most, and i love formal logic, so i thought i'd write a proof assistant in PHP !
It comes with three new classes : `wff`, `sequent`, `application`.
### `wff` class
Structurally, an instance of `wff` is comprised of a `protected string $expression` and a `protected array $construction`.
- **Construction** : `$p = new wff("p")`, only takes a string argument with alphanumeric symbols, -, ., and _.
- **`$p->turnIntoConstant()`** : turns `$p` into a constant.
- **Operations** : `wff::and(wff $a, wff $b)`, `wff::or(wff $a, wff $b)`, `wff::implies(wff $a, wff $b)`, `wff::not(wff $a)`
- **First-order logic** : `wff::predicate(string $a, wff $b)`, `wff::forall(string $a, wff $b)`, `wff::forall(string $a, wff $b)`, with `$a` only comprised alphanumeric symbols, -, ., and _.

### `sequent` class
Structurally, an instance of `sequent` is comprised of a `protected array $premises` of wff instances, and a `protected wff $conclusion`.
- **Construction** : `$s = new sequent(array $a, wff $b)` where `$a` is an array of wff instances (it **will** be checked).
- **Methods** : `$s->getPremises()` and `$s->getConclusion()` enables you to access both variables.

### `application` class
Structurally, an instance of `application` is comprised of a `protected sequent $application`, a `protected array $proof`, a `protected string $name`, and a `protected bool $issorry`.

- **Construction** : `$t = new application(sequent $a, string $name)`. It automatically generates the start of its proof, with as many lines as `$a`'s premises.
- **`$t->print()`** : prints the application _with_ its proof, not just its sequent like its `strval` does.
- **`$t->startSubproof(wff $a)`** : adds a new premise `$a` on top of `$t`'s proof's last step's premises, generating a new step in said proof along the way.

There are quite a few **rules of inference**, which aren't always _exactly_ how they're formalized in natural deduction but eh, that's close enough.
- **`->conjunctionIntroduction(int $a, int $b)`** : uses the conclusions in lines `$a` and `$b` to conclude their conjunction (`wff::and`), given the combined premises of both lines.
- **`->conjunctionEliminationLeft(int $a)`** : If the conclusion in line `$a` is a conjunction, concludes its left-hand side given its premises.
- **`->conjunctionEliminationRight(int $a)`** : If the conclusion in line `$a` is a conjunction, concludes its right-hand side given its premises.
- **`->disjunctionIntroductionleft(int $a, wff $b)`** : Within the premises in line `$a`, concludes the disjunction of its conclusion with `$b`, in that order.
- **`->disjunctionIntroductionleft(int $a, wff $b)`** : Within the premises in line `$a`, concludes the disjunction of `$b` with its conclusion, in that order.
- **`->disjunctionElimination(int $a, int $b, $c)`** : Within the combined premises of lines `$a`, `$b`, and `$c`, it checks whether the conclusion at line `$a` is disjunctive, conditional at line `$b`, and conditional at line `$c`, such that if the consequents of line `$b`'s and `$c`'s conclusions are identical (let's call it `d`), such that the antecedent at line `$b`'s conclusion is the left-hand side of `$a`'s conclusion, and such that antecedent of line `$c`'s conclusion is the right-hand side of `$a`'s conclusion, and finally concludes `d`. Yeah, that's a mouthful.
- **`->conditionalIntroduction(int $a)`** : Within the premises of line `$a` minus the last one, concludes the `wff::implies` of line `$a`'s last premise and line `$a`'s conclusion.
- **`->conditionalElimination(int $a, int $b)`** : Within the combined premises of lines `$a` and `$b`, checks if line `$a`'s conclusion is conditional and that its antecedent is the same as line `$b`'s conclusion, and finally concludes the consequent of line `$a`'s conclusion.
- **`->negationIntroduction(int $a, int $b)`** : Within the combined premises of lines `$a` and `$b`, checks if both lines' conclusions are conditional, have the same antecedent and that the consequent in line `$a`'s conclusion is the negation of the consequent in line `$b`'s conclusion, before concluding the negation of the shared antecedent between both lines' conclusions.
- **`->negationElimination(int $a, int $b, wff $c)`** : Within the combined premises of lines `$a` and `$b`, checks if line `$a`'s conclusion is the negation of line `$b`'s conclusion, and concludes `$c` (principle of explosion).
- **`->existentialIntroduction(wff $a, string $b, int $c)`** : Within the premises of line `$c`, checks if `$b` is an usable variable name, makes it the `string` of an existential quantifier, in which the quantified formula is that of line `$c`'s conclusion where all instances of `$a` are replaced by `new wff($b)`.
- **`->existentialElimination(string $a, int $b)`** : Within the premises of line `$b`, checks if line `$b`'s conclusion is an existential statement and whether `$a` is an adequate constant expression, and yeets the quantifier by concluding its formula once the quantified variabled was replaced by a constant of expression `$a`.
- **`->universalIntroduction(string $a, string $b, int $c)`** : Within the premises of line `$c`, checks if `$a` is not the expression of a constant and that `$b` is an adequate variable expression, before concluding an universal quantifier with string `$b` and replacing every instance of the variable of expression `$a` by a variable of expression `$b`.
- **`->universalElimination(string $a, int $b)`** : Within the premises of line `$b`, checks if line `$b`'s conclusion is an universal statement and whether `$a` is an adequate variable expression, and yeets the quantifier by concluding its formula once the quantified variabled was replaced by a variable of expression `$a`.
- **`->eval(application $t, array $lines)`** : Not a rule of inference _per se_, simply evaluates an earlier application `$t` that requires the lines stored at `$lines` in the right order.
- **`->sorry()`** : Shows that we don't know how to prove an application, or haven't proved it yet ; converts the `issorry` variable to true.

Here are examples of codes that implement fully-fletched theorems, with proofs and everything :
```php
function doubleNegationElimination (wff $p) : application {
	$premises[0] = wff::not(wff::not($p));
	$conclusion = $p;
	$sequent = new sequent($premises,$conclusion);
	$theorem = new application($sequent,'DNE');
	
	// no proof in intuitionistic logic... sowwy ! >w<
	$theorem->sorry()
	;return $theorem;
}

function DeMorganND2CN (wff $p, wff $q) : application {
	$premises[0] = wff::not(wff::or($p,$q));
	$conclusion = wff::and(wff::not($p),wff::not($q));
	$sequent = new sequent($premises,$conclusion);
	$theorem = new application($sequent,'DeM');
	
	// proof
	$theorem->startSubproof($p)
		->disjunctionIntroductionLeft(1,$q)
		->conjunctionIntroduction(0,2)
		->conjunctionEliminationLeft(3)
		->conditionalIntroduction(4)
		->conditionalIntroduction(2)
		->negationIntroduction(5,6)
		->startSubproof($q)
		->disjunctionIntroductionRight(8,$p)
		->conjunctionIntroduction(0,9)
		->conjunctionEliminationLeft(10)
		->conditionalIntroduction(11)
		->conditionalIntroduction(9)
		->negationIntroduction(12,13)
		->conjunctionIntroduction(7,14)
	;return $theorem;
}

function lawOfExcludedMiddle (wff $p) : application {
	$conclusion = wff::or($p,wff::not($p));
	$sequent = new sequent([],$conclusion);
	$theorem = new application($sequent,'LEM');
	
	// proof
	$theorem->startSubproof(wff::not(wff::or($p,wff::not($p))))
		->eval(DeMorganND2CN($p,wff::not($p)),[0])
		->conjunctionEliminationRight(1)
		->conjunctionEliminationLeft(1)
		->conditionalIntroduction(2)
		->conditionalIntroduction(3)
		->negationIntroduction(4,5)
		->eval(doubleNegationElimination(wff::or($p,wff::not($p))),[6])
	;return $theorem;
}
```
