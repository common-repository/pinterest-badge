function pinBadgeAddLoadEvent(func) {
  if (window.addEventListener)
    window.addEventListener("load", func, false);
  else if (window.attachEvent)
    window.attachEvent("onload", func);
  else { // fallback
    var old = window.onload;
    window.onload = function() {
      if (old) old();
      func();
    };
  }
}
pinBadgeAddLoadEvent(function(){var foll=document.getElementById('pinbadgeFollowerCount');if(foll) {foll.style.display="block";}var credit=document.getElementById('pinbadgeCredit');if(credit) {credit.style.display="none";}});
