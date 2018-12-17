const app = new Vue({

}).$mount('#app')


var inputs = document.querySelectorAll('input')
var theForm = document.getElementById("GoodForm")
var question = document.querySelectorAll('.faqQuestion')

theForm.addEventListener('focus', function(event){
  theirParent = event.target.parentNode
  classes = theirParent.classList
  theirParent.classList = classes + " inputFocussed"
}, true)

theForm.addEventListener("blur", function( event ) {
  theirParent = event.target.parentNode
  theirParent.classList = "inputWrapper"
}, true);


new BadgerAccordion('.js-badger-accordion', {
});
// const faqQuestions = document.querySelectorAll('.faqQuestion')
// const faqAnswerContent = document.querySelectorAll('.faqAnswerContent')
//
//
// function addClass(el, klass) {
//   el.classList.add(klass);
// }
//
// function removeClass(el, klass) {
//   el.classList.remove(klass);
// }
//
// faqQuestions.forEach(function(faq){
//   faq.classList.add('hidden')
//   var faqQuestion = faq.children[0]
//   faqQuestion.addEventListener('click', showFaq)
//   function showFaq(event) {
//     event.target.parentElement
//   }
// })
