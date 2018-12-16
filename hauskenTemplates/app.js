
const About = { template: '<div>About</div>' }
const Registration = { template: '<div>Registration</div>' }


const routes = [
  { path: '/about', component: About },
  { path: '/registration', component: Registration }
]

const router = new VueRouter({
  routes: [
    {
      path: '/about',
      name: 'About',
      component: About
    },
    {
      path: '/registration',
      name: 'Registration',
      component: Registration
    }
  ]
})

const app = new Vue({
  router
}).$mount('#app')


var inputs = document.querySelectorAll('input')
var theForm = document.getElementById("GoodForm")

theForm.addEventListener('focus', function(event){
  theirParent = event.target.parentNode
  theirParent.classList = "inputWrapper inputFocussed"
}, true)

theForm.addEventListener("blur", function( event ) {
  theirParent = event.target.parentNode
  theirParent.classList = "inputWrapper"
}, true);

inputs.forEach(function(item){

})
