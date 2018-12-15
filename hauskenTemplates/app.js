
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
