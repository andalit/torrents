// Sample usage alert(roundit(23.3353, 2)) [http://javascriptkit.com/script/script2/roundnum.shtml]
function roundit(Num, Places) {
	if (Places > 0) {
		if ((Num.toString().length - Num.toString().lastIndexOf('.')) > (Places + 1)) {
			var Rounder = Math.pow(10, Places);
			return Math.round(Num * Rounder) / Rounder;
		}
		else return Num;
	}
	else return Math.round(Num);
}

function str_replace(s, r, sbj) {
	/*
	**  Replace a token in a string
	**    s  token to be found and removed
	**    r  token to be inserted
	**    sbj  string to be processed
	**  returns new String
	*/
	i = sbj.indexOf(s);
	r = '';
	if (i == -1) return sbj;
	r += sbj.substring(0,i) + r;
	if (i+s.length < sbj.length) {
		r += replace(sbj.substring(i + s.length, sbj.length), s, r);
	}
 return r;
}

jQuery (function($) {
 $("form").submit(function(){ $(this).find("input[@type='submit']").attr("value", "Loading..."); });
 startList();
});