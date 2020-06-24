function checkForm(formId){
	theForm=document.getElementById(formId);
	requiredArray= theForm.required.value.split(',');
	missing=0;
	for(var i in requiredArray)
	{
		if(theForm[requiredArray[i]].value == "" || theForm[requiredArray[i]].value == "<br>")
		{
			document.getElementById(requiredArray[i]).setAttribute("onblur","javascript: if(this.value.length>0){this.style.border='';}");	
			document.getElementById(requiredArray[i]).style.border="1px solid red";
			if(document.getElementById(requiredArray[i]+"_title"))
				document.getElementById(requiredArray[i]+"_title").style.color="red";
			if(missing==0)theForm[requiredArray[i]].focus();
			missing=1;
		}
	}
	if(missing==1)
	{
		alert("You did not fill in all fields, please try again.");
		return false;
	}
}
function decision(message, url){
if(confirm(message)) location.href = url;
}
function multiCheck(thisform,field,thisbox)//multiCheck(this.form,'oid[]',this)
{
	var boxes=thisform.elements[field];
	if(!boxes)
		return;
	for (var i in boxes)
		boxes[i].checked = thisbox.checked;
}
function togglesection(sid)
{
	sec=document.getElementById(sid);
	tog=document.getElementById(sid.replace('sec','tog'));
	if(sec.style.display!='none')
	{	sec.style.display='none';tog.innerHTML='+';}
	else
	{	sec.style.display='';tog.innerHTML='-';}
}