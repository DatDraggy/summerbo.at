const app = new Vue({

}).$mount('#app')


var inputs = document.querySelectorAll('input')
var theForm = document.getElementById("GoodForm")

theForm.addEventListener('focus', function(event){
  theirParent = event.target.parentNode
  classes = theirParent.classList
  theirParent.classList = classes + " inputFocussed"
}, true)

theForm.addEventListener("blur", function( event ) {
  theirParent = event.target.parentNode
  theirParent.classList = "inputWrapper"
}, true);

inputs.forEach(function(item){

})
