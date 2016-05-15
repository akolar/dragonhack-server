// ==UserScript==
// @name         New Userscript
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  try to take over the world!
// @author       You
// @match        https://urnik.fri.uni-lj.si/timetable/2015_2016_letni/allocations?student=63150029
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
   if(document.getElementById("id_timetable")!==null){
        document.body.innerHTML="<div id='export' style='height: 55px;width: 180px;background-color: rgb(210, 169, 247); position:absolute;top: 60px;left: 420px;padding: 10px;box-shadow: 0px 0px 2px 1px;color: rgba(45,70,5,1);'>Export</div>"+document.body.innerHTML;
        var myDiv   = document.querySelector ("#export");
        if (myDiv) {
            myDiv.addEventListener ("click", funct , false);
        }
   }
})();

 function funct (event) {
     var data=[{"day":"2","0":"2","id":"33","1":"33","hour":"8","2":"8","room":"PR15","3":"PR15"},{"day":"4","0":"4","id":"79","1":"79","hour":"9","2":"9","room":"PR15","3":"PR15"},{"day":"3","0":"3","id":"101","1":"101","hour":"10","2":"10","room":"K1129","3":"K1129"},{"day":"2","0":"2","id":"122","1":"122","hour":"11","2":"11","room":"PR15","3":"PR15"},{"day":"1","0":"1","id":"134","1":"134","hour":"12","2":"12","room":"PR09","3":"PR09"},{"day":"2","0":"2","id":"176","1":"176","hour":"13","2":"13","room":"PR09","3":"PR09"},{"day":"4","0":"4","id":"192","1":"192","hour":"13","2":"13","room":"PR16","3":"PR16"},{"day":"2","0":"2","id":"233","1":"233","hour":"15","2":"15","room":"PR09","3":"PR09"},{"day":"4","0":"4","id":"252","1":"252","hour":"15","2":"15","room":"PR15","3":"PR15"},{"day":"2","0":"2","id":"314","1":"314","hour":"8","2":"8","room":"PR15","3":"PR15"},{"day":"4","0":"4","id":"360","1":"360","hour":"9","2":"9","room":"PR15","3":"PR15"},{"day":"3","0":"3","id":"382","1":"382","hour":"10","2":"10","room":"K1129","3":"K1129"},{"day":"2","0":"2","id":"403","1":"403","hour":"11","2":"11","room":"PR15","3":"PR15"},{"day":"1","0":"1","id":"415","1":"415","hour":"12","2":"12","room":"PR09","3":"PR09"},{"day":"2","0":"2","id":"457","1":"457","hour":"13","2":"13","room":"PR09","3":"PR09"},{"day":"4","0":"4","id":"473","1":"473","hour":"13","2":"13","room":"PR16","3":"PR16"},{"day":"2","0":"2","id":"514","1":"514","hour":"15","2":"15","room":"PR09","3":"PR09"},{"day":"4","0":"4","id":"533","1":"533","hour":"15","2":"15","room":"PR15","3":"PR15"}];
     var csv="Subject,Start Date,Start Time,End Date,End Time,All Day Event,Location\n";
     for(var i=0;i<data.length;++i){
         var months=[31,28,31,30,31,30,31,31,30,31,30,31];
         var d=new Date();
         var day=d.getDay();
         var dayim=d.getDate();
         var month=d.getMonth();
         for(var p=0;p<5;++p){
             while(day<parseInt(data[i].day)){
                 ++day;
                 ++dayim;
                 day%=7;
                 if(dayim>months[month]){
                     ++month;
                     dayim=1;
                 }
             }
             csv+=data[i].id+","+(month<9?"0":"")+(month+1)+"/"+(dayim<10?"0":"")+dayim+"/2016,"+(((parseInt(data[i].hour)-1)%12)+1)+":00 "+(parseInt(data[i].hour)>=12?"PM,":"AM,")+(month<9?"0":"")+(month+1)+"/"+(dayim<10?"0":"")+dayim+"/2016,"+(((parseInt(data[i].hour)+1)%12)+1)+":00 "+(parseInt(data[i].hour)+2>=12?"PM,":"AM,")+"False,FRI\n";
             dayim+=7;
             if(dayim>months[month]){
                ++month;
                 if(month>11){
                     month=0;
                 }
                dayim%=months[month-1];
             }
         }
     }
     window.open('data:text/csv;charset=utf-8,' + escape(csv));
}