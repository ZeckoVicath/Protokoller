function getPid (name) {
	return name.replace(/ /g, "_");
}
function dePid (pid) {
	return pid.replace (/_/g, " ");
}
function generatePersonRow (name) {
	var substname = getPid(name);
	var tr = document.createElement("tr");
	var td1 = document.createElement("td");
	var checkbox = document.createElement("input");
	checkbox.setAttribute("type","checkbox");
	checkbox.setAttribute("id",substname+"_present");
	checkbox.setAttribute("name",substname+"_present");
	checkbox.setAttribute("onchange",checkCount);
	td1.appendChild(checkbox);
	td1.appendChild(document.createTextNode(name));
	tr.appendChild(td1);
	var td2 = document.createElement("td");
	var from_inp = document.createElement("input");
	from_inp.setAttribute("type","text");
	from_inp.setAttribute("size",10);
	from_inp.setAttribute("id",substname+"_from");
	from_inp.setAttribute("name",substname+"_from");
	from_inp.addEventListener("change",function(){setChecked(substname)});
	td2.appendChild(from_inp);
	var from_btn = document.createElement("input");
	from_btn.setAttribute("type","button");
	from_btn.setAttribute("value","now");
	from_btn.addEventListener("click",function(){setChecked(substname);setTimeNow(substname+"_from");});
	td2.appendChild(from_btn);
	tr.appendChild(td2);
	var td3 = document.createElement("td");
	var till_inp = document.createElement("input");
	till_inp.setAttribute("type","text");
	till_inp.setAttribute("size",10);
	till_inp.setAttribute("id",substname+"_till");
	till_inp.setAttribute("name",substname+"_till");
	till_inp.addEventListener("change",function(){setChecked(substname)});
	td3.appendChild(till_inp);
	var till_btn = document.createElement("input");
	till_btn.setAttribute("type","button");
	till_btn.setAttribute("value","now");
	till_btn.addEventListener("click",function(){setChecked(substname);setTimeNow(substname+"_till");});
	td3.appendChild(till_btn);
	tr.appendChild(td3);
	return tr;
}
function addPerson (name) {
	var node = generatePersonRow(name);
	document.getElementById("attendance_table").appendChild(node);
}
function addGuest () {
	var naminp = document.getElementById("newGuestName");
	var guestname = "";
	if (naminp.value == "") {
		guestname=prompt("name of the guest");
	} else {
		guestname=naminp.value;
	}
	addPerson(guestname);
	setChecked(getPid(guestname));
	checkCount();
	naminp.value="";
}
function createPersonList () {
	var inps = document.getElementsByTagName("input");
	var list = new Array ();
	for (var i=0; i<inps.length; i++) {
		var el = inps[i];
		if (el.type=="checkbox") {
			if (el.checked && endsWith(el.name,"_present")) {
				list.push(dePid(el.name.substring(0,el.name.length-8)));
			}
		}
	}
	return list;
}
function getPersonList (elem) {
	var list = createPersonList();
	elem.innerHTML = ""; // clear
	for (var i=0; i<list.length; i++) {
		elem.innerHTML += "<option>"+list[i]+"</option>";
	}
}
function checkCount () {
	var elems = document.getElementsByTagName("input");
	var present_count = 0;
	for (var i=0; i<elems.length; i++) {
		var elem = elems[i];
		if (endsWith(elem.id,"_present") && elem.checked) {
			present_count++;
		}
	}
	document.getElementById("people_counter").innerHTML = present_count;
}
function endsWith (haystack, needle) {
	return haystack.indexOf(needle, haystack.length - needle.length) !== -1;
}
function today() {
	var time = new Date();
	var hours = new String(time.getHours());
	var minutes = new String(time.getMinutes());
	hours = hours < 10 ? "0" + hours : hours;
	minutes = minutes < 10 ? "0" + minutes : minutes;
	return hours + ":" + minutes;
}
function setTimeNow(element_id) {
	document.getElementById(element_id).value = today();
}

function setChecked(person_id) {
	document.getElementById(person_id + '_present').setAttribute('checked', 'checked');
}

function generateNewTops(n) {
	lastTop = tops;
	tops = tops + n;
	for (var i = lastTop + 1; i <= lastTop + n; i++) {
		var newTop = document.createElement("div");

		var newHeading = document.createElement("h3");
		newHeading.appendChild(document.createTextNode("item " + i));
		newTop.appendChild(newHeading);

		var newInputHeadingLabel = document.createElement("label");
		newInputHeadingLabel.appendChild(document.createTextNode(unescape("headline: ")));
		newInputHeadingLabel.setAttribute("for", "top" + i + "_heading");
		newTop.appendChild(newInputHeadingLabel);

		var newInputHeading = document.createElement("input");
		newInputHeading.setAttribute("type", "text");
		newInputHeading.setAttribute("size", "60");
		newInputHeading.setAttribute("name", "top" + i + "_heading");
		newInputHeading.setAttribute("id", "top" + i + "_heading");
		newTop.appendChild(newInputHeading);

		var newBreak = document.createElement("br");
		newTop.appendChild(newBreak);

		var newTextarea = document.createElement("textarea");
		newTextarea.setAttribute("style", "margin-bottom:20px;");
		newTextarea.setAttribute("cols", "85");
		newTextarea.setAttribute("rows", "8");
		newTextarea.setAttribute("name", "top" + i);
		newTextarea.setAttribute("id", "top" + i);
		newTop.appendChild(newTextarea);

		var tops_div = document.getElementById("tops_div");
		tops_div.appendChild(newTop);
	}
}
