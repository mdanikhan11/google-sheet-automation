<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Firebase Auth: Google - TechFerment</title>
  </head>
  <body>
    <a href="{{route('login')}}">Login</a>
    <h1>Welcome : Firebase Auth: Google</h1>
    <p>TechFerment: Firebase For Web</p>

    <div id="LoginScreen">
      <button id="login">Login with Google</button>
    </div>

    <div id="dashboard">
      <div id="userDetails"></div>
      <button id="logout">Logout</button>
    </div>

    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/8.0.1/firebase-app.js"></script>

    <script src="https://www.gstatic.com/firebasejs/8.0.0/firebase-auth.js"></script>

    <!-- TODO: Add SDKs for Firebase products that you want to use
     https://firebase.google.com/docs/web/setup#available-libraries -->

    <script>
      // Your web app's Firebase configuration
      var firebaseConfig = {
        apiKey: "AIzaSyDYGHq9B8__Qa6xsK7bz6nB1X0L-Vkgpe4",
        authDomain: "test-328cf.firebaseapp.com",
        projectId: "test-328cf",
        storageBucket: "test-328cf.appspot.com",
        messagingSenderId: "622322211348",
        appId: "1:622322211348:web:a0ba891bcfab0fe188e501"
      };
      // Initialize Firebase
      firebase.initializeApp(firebaseConfig);

      document.getElementById('dashboard').style.display="none"

      document.getElementById('login').addEventListener('click', GoogleLogin)
      document.getElementById('logout').addEventListener('click', LogoutUser)

      let provider = new firebase.auth.GoogleAuthProvider()

      function GoogleLogin(){
        console.log('Login Btn Call')
        firebase.auth().signInWithPopup(provider).then(res=>{
            console.log(res);
          console.log(res.user)
          document.getElementById('LoginScreen').style.display="none"
          document.getElementById('dashboard').style.display="block"
          showUserDetails(res.user)
        }).catch(e=>{
          console.log(e)
        })
      }

      function showUserDetails(user){
        document.getElementById('userDetails').innerHTML = `
          <img src="${user.photoURL}" style="width:10%">
          <p>Name: ${user.displayName}</p>
          <p>Email: ${user.email}</p>
        `
      }

      function checkAuthState(){
        firebase.auth().onAuthStateChanged(user=>{
            console.log(user);
          if(user){
            document.getElementById('LoginScreen').style.display="none"
            document.getElementById('dashboard').style.display="block"
            showUserDetails(user)
          }else{

          }
        })
      }

      function LogoutUser(){
        console.log('Logout Btn Call')
        firebase.auth().signOut().then(()=>{
          document.getElementById('LoginScreen').style.display="block"
          document.getElementById('dashboard').style.display="none"
        }).catch(e=>{
          console.log(e)
        })
      }
      checkAuthState()
    </script>
  </body>
</html>
