function convertdate (date,func) 
{
	if (date == '')
	{ return ''; }
	else
	{
		if (func == 'tomysql')
		{ //insert conversion to MySQL database
		var atoms = date.split("-");
		var month = atoms[0]; var day = atoms[1]; var year = atoms[2];
		date = "" + year + "-" + month + "-" + day + "";
		return date;
		}
		else if (func == 'touser')
		{ //output conversion to User
		atoms = date.split("-");
		year = atoms[0]; month = atoms[1]; day = atoms[2];
		date = "" + month + "-" + day + "-" + year + "";
		return date;
		}
	}
}

function convertphone (pn,func)
{
	output = '';
	if (func == "touser") {
		//console.log(pn[0]+pn[1]+pn[2]);
		//output = string('('+pn[0]+pn[1]+pn[2]+') '+pn[3]+pn[4]+pn[5]+'-'+pn[6]+pn[7]+pn[8]+pn[9]);
	} else if (func == "tomysql") {
		//output = pn.replace(/[^\d]/g, "");
	}
	return output;
}