// ==UserScript==
// @name         New Userscript
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  try to take over the world!
// @author       You
// @match        http://*/*
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
    if(document.getElementById("id_timetable")!==null){

    document.body.innerHTML="<div id='zamenjave' style='height: 60px;width: 180px;background-color: rgb(210, 169, 247); position:absolute;top: 55px;left: 200px;padding: 10px;box-shadow: 0px 0px 2px 1px;color: rgba(45,70,5,1);'>Zamenjave vaj</div>"+document.body.innerHTML;
   var myDiv   = document.querySelector ("#zamenjave");
    if (myDiv) {
        myDiv.addEventListener ("click", myfunc , false);

    }}
})();

 function myfunc (zEvent) {
   document.location.href='http://localhost:8080/';
}