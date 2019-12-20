<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Sunny i3">
  <title>Chatbot</title>

  <!-- Insert style -->

<link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i"
    rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<link rel="stylesheet/less" type="text/css" href="less/styles.css" />
  <!-- END :: Insert style -->

  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
 <!--  <script src="scripts/jquery-3.3.1.js" type="text/javascript"></script> -->
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script src="scripts/3rdparty/jquery-xml2json/jquery.xml2json.js" type="text/javascript"></script>
    <script src="scripts/main.js" type="text/javascript"></script>
    <script src="scripts/constants.js" type="text/javascript"></script>
    <script src="scripts/rest-util.js" type="text/javascript"></script>
</head>
</head>

<body style="background-color: transparent; border: 0px;border-color: transparent">
  <div class="background-page"> <!-- class="background-page" -->
    <div class="box" style="display: block;" id="box_chat">
      <div class="msg-header">
        <div class="com-name">
          <h5>i3 international</h5>
        </div>
        <div class="msg-header-img btn-group" role="group" aria-label="Basic example">
           <button type="button" class="per-icon">
            <img src="img/v1.png" alt="">
            <p>Sunny</p>
          </button>
          <button type="button" class="per-icon">
            <img src="img/v2.png" alt="">
            <p>Bella</p>
          </button>
          <button type="button" class="per-icon">
            <img src="img/v3.png" alt="">
            <p>Nguyen</p>
          </button>
      </div>
    </div>
    <div class="chat-page">
      <div class="msg-inbox">
        <div class="chat" id="chatbox-history">
          <div class="msg-page" id="messagesBody">
        <!-- Content Here -->
          </div>
          <div class="msg-bottom">
            <div >
              <input type="text" class="form-control input-style sendMsg" placeholder="Write a reply">
              <div class="icons btn-group" role="group" aria-label="Basic example">
                  <img src=" img/ic_tag_faces_24px.svg" alt="">
                <img src="img/ic_attachment_24px.svg" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <a onclick=on_off() class="float">
    <i class="fa fa-plus my-float">
      <img src="img/f-comment.svg" alt="">
    </i>
  </a>
</div>
  <!-- Start script -->
<!--   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
    integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
  </script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
    integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
  </script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <script src="less/less.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.carousel').carousel();      
      // $('.background-page').load("www.google.com");        
                       
    });

  </script>
  <!-- END :: script-->
</body>
</html>

<script type="text/javascript">
      $('.sendMsg').bind("enterKey",function(e){
        var msg = $('.sendMsg').val();
        if (msg.replace(/^\s+|\s+$/g, "").length != 0){
          
           createMsgOut(decodeString(msg));
         
           if(session.startChat == undefined){

                StartChat();
           
           }else if(session.startChat != undefined){
           
             restUtil.putChatMessage(msg);
           
           }
           
           $(".sendMsg").val("");
        }
      });
      $('.sendMsg').keyup(function(e){
      if(e.keyCode == 13)
      {
        $(this).trigger("enterKey");
      }
      });
      function on_off() {
  var x = document.getElementById("box_chat");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

</script>
<script>

</script>
