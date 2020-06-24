emailereg = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$/;
function checkForm(formId){
	theForm=document.getElementById(formId);
	requiredArray = theForm.required.value.split(',');
	missing=0;
	emailError=0;
	for(var i in requiredArray)
	{
		theField=theForm[requiredArray[i]];
		theValue=theField.value;
		if((theValue.length<1 || theValue == "<br>" || (theField.id.search(/email/i) != -1 && !emailereg.test(theValue))) && theField)
		{
			if(theField.id.search(/email/i) != -1 && !emailereg.test(theValue) && theValue.length>0){emailError=1;}
			theField.setAttribute("onblur","javascript: if(this.value.length>0){this.style.border='';}");	
			theField.style.border="1px solid red";
			if(theForm[requiredArray[i]+"_title"])
				theForm[requiredArray[i]+"_title"].style.color="red";
			//if(missing==0)theField.focus();
			missing=1;
		}
	}
	if(missing==1)
	{
		
		alert("You did not fill in all fields, please try again."+(emailError==1?"\r\nInvalid email address. Please use user@host.com format.":""));
		return false;
	}
}
function decision(message, url){
if(confirm(message)) location.href = url;
}
//function isArray(obj){return(typeof(obj.length)=="undefined")?false:true;}

var totalHeight;
var rollOpen;
var rollClose;
function divroller(divId,eventName)
{
	divObj=document.getElementById(divId);
	totalHeight=document.getElementById(divId).scrollHeight;
	if(divObj.offsetHeight>0&&eventName=="mouseout")
		{clearTimeout(rollOpen);divrollclose();}
	else
		{clearTimeout(rollClose);divrollopen();}
}
function divrollopen()
{
	if(divObj.offsetHeight<totalHeight)
	{
		divObj.style.height=divObj.offsetHeight+3+"px";
		rollOpen=setTimeout("divrollopen()",0);
	}
}
function divrollclose()
{
	if(divObj.offsetHeight>0)
	{
		divObj.style.height=divObj.offsetHeight-3+"px";
		rollClose=setTimeout("divrollclose()",0);
	}
}
var boxarr=[];
var boxes=[];
function cart_multiCheck(thisform,field,thisbox)//multiCheck(this.form,'oid[]',this)
{
	boxes=thisform.elements[field];
	if(!boxes && boxarr[field].length>0)
	{	
		var boxes=[];
		for(var x in boxarr[field])
			boxes.push(thisform.elements[boxarr[field][x]][1]);
	}
	if(!boxes)
		return;
	for (var i in boxes)
		boxes[i].checked = thisbox.checked;
}
function cartUpdateViews(pid)
{
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	//xmlhttp.open("GET","ajax.php?q="+escape(str)+"&k="+kid,true);
	xmlhttp.open("GET","content/js/ajax.php?q=views&p="+pid,true);
	xmlhttp.send();
}