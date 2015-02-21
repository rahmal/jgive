function selectstatusorder(appid,ele)
{
	var selInd=ele.selectedIndex;
	var status =ele.options[selInd].value;
	document.getElementById('hidid').value = appid;
	document.getElementById('hidstat').value = status;
	submitbutton('save');
   	return;
}