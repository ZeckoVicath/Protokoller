var top_increment = 1;
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

function generateNewTops(lastTop) {
        tops = tops + top_increment;
	for (var i = lastTop + 1; i <= lastTop + top_increment; i++) {
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
		
		var newReplaceDiv = document.createElement("div");
		newReplaceDiv.setAttribute("id", "replace_me");

		var newInputTopNr = document.createElement("input");
		newInputTopNr.setAttribute("type", "hidden");
		newInputTopNr.setAttribute("name", "top_number");
		newInputTopNr.setAttribute("id", "top_number");
		newInputTopNr.setAttribute("value", i);
		newReplaceDiv.appendChild(newInputTopNr);
		
		var tops_div = document.getElementById("tops_div");
		var replace_me = document.getElementById("replace_me");
		
		tops_div.replaceChild(newReplaceDiv, replace_me);
		tops_div.insertBefore(newTop, newReplaceDiv);
	}
}
