


// Used in all pages to submit a form and optionally set a hidden 
// form varaible called 'navigate' to direct navgiation
function submitForm(formName, navigateValue) {
	if (navigateValue != null && navigateValue != "") {
		document.forms[formName].navigate.value = navigateValue;
	}
    document.forms[formName].submit();
}
var curCount=1;
function timerStart(divId)
{
	setInterval('counter("'+divId+'")',1000);
}
function counter(divId)
{
	curSecs=curCount>59?curCount%60:curCount;
	curSecs=curSecs<10?0+""+curSecs:curSecs;
	curMins=curCount>59?(curCount-(curCount%60))/60:0;
	document.getElementById(divId).innerHTML=curMins+":"+curSecs;
	curCount+=1;
}