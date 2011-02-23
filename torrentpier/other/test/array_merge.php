$a=array(1,'s'=>'aa')
: array =
  0: long = 1
  s: string = "aa"

$b=array(2,'s'=>'bb')
: array =
  0: long = 2
  s: string = "bb"
_____________________

$a+$b
: array =
  0: long = 1
  s: string = "aa"

array_merge($a,$b)
: array =
  0: long = 1
  s: string = "bb"
  1: long = 2

array_merge_recursive($a,$b)
: array =
  0: long = 1
  s: array =
    0: string = "aa"
    1: string = "bb"
  1: long = 2

