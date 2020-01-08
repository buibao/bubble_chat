<script type="text/javascript">
    var urlConfig = "<?php echo base_url() . 'plugins/chat/bubble_chat/config/config.json'?>";
    var urlImage = "<?php echo base_url() . 'plugins/chat/bubble_chat/img/';?>";
    var urlPlugins = "<?php echo base_url() . 'plugins/chat/';?>";
</script>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Sunny i3">
    <title>Chatbot</title>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css"> -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet/less" type="text/css" href="<?php echo base_url() . 'plugins/chat/bubble_chat/less/styles.css';?>" /> 
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js" type="text/javascript"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="<?php echo base_url() . 'plugins/chat/bubble_chat/scripts/3rdparty/jquery-xml2json/jquery.xml2json.js'; ?>" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script src="<?php echo base_url() . 'plugins/chat/javascripts/utils.js';?>"></script>
    <script src="<?php echo base_url() . 'plugins/chat/javascripts/customer_chat.js';?>"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x2js/1.2.0/xml2json.min.js"></script>
    <script src="<?php echo base_url() . 'plugins/chat/bubble_chat/scripts/main.js';?>" type="text/javascript"></script>
    <script src="<?php echo base_url() . 'plugins/chat/bubble_chat/scripts/constants.js';?>" type="text/javascript"></script>
    <script language="JavaScript">
    /**
    * Plays a sound using the HTML5 audio tag. Provide mp3 and ogg files for best browser support.
    * @param {string} filename The name of the file. Omit the ending!
    */
     function playSound(action){

        // ======================================Start Chat============================================

        var mp3SourceStart = '<source src="'+ urlPlugins + '/sound/' + config.sound.start + '.mp3" type="audio/mpeg">';
        var oggSourceStart = '<source src="'+ urlPlugins + '/sound/' + config.sound.start + '.ogg" type="audio/ogg">';

        // ======================================End Chat==============================================

        var mp3SourceEnd = '<source src="'+ urlPlugins + '/sound/' + config.sound.end + '.mp3" type="audio/mpeg">';
        var oggSourceEnd = '<source src="'+ urlPlugins + '/sound/' + config.sound.end + '.ogg" type="audio/ogg">';

        switch(action) {

          case "start":
             document.getElementById("sound").innerHTML='<audio autoplay="autoplay">' + mp3SourceStart + oggSourceStart + '</audio>';
            break;
          case "end":
             document.getElementById("sound").innerHTML='<audio autoplay="autoplay">' + mp3SourceEnd + oggSourceEnd + '</audio>';
            break;
          default:
            // code here
        }
                   
    }              
    </script>
    <script language="JavaScript">

        var socialMinerChat = new socialminer.chat();
        // socialMinerChat.init(window.location.protocol + "//" + window.location.host, "https://hq-socialminer.abc.inc/ccp/feed/100040");
        var agentName = null;
        var contact = {};
        var messageCount = 0;
        var updateWaitMessageTimeoutHandle = null;
        var lastTypingEvent = null;

        var CHAT_UI_STATES = {
            ENTERING_INFO: "ENTERING_INFO",
            WAITING_FOR_AGENT: "WAITING_FOR_AGENT",
            CHATTING: "CHATTING",
            TRANSCRIPT_PROMPT: "TRANSCRIPT_PROMPT",
            END: "END"
        };

        function processStatusEvent(event) {
            var endChat = false;

            if (event.status == "chat_ok") {
                if (agentName) {
                    // success("Chatting with " + event.from);
                    createMsgIncome("Chating with " + event.from, "Bot");
                }
            } else if (event.status == "chat_issue") {
                // The message in the detail field is not localized or internationalized. If this is necessary, use the
                // event.status field as the key in your message bundles.
                //
                // info(event.detail);
                createMsgIncome(event.detail, "Bot");
                endChat = true;
            } else if (event.status == "chat_request_rejected_by_agent") {
                // This status signals that there are no chat agents logged in.
                //
                // info("Sorry, all customer care representatives are busy. Please try back at a later time.");
                createMsgIncome("Sorry, all customer care representatives are busy. Please try back at a later time.", "Bot");
                endChat = true;
            } else if (event.status == "chat_timedout_waiting_for_agent") {
                // This status signals that there are agents logged into CCX, but none are available to chat.
                //
                // info("All customer care representatives are busy assisting other clients. Please continue to wait or try again later.");
                createMsgIncome("All customer care representatives are busy assisting other clients. Please continue to wait or try again later.", "Bot");
                endChat = true;
            } else {
                // warning(event.detail);
                createMsgIncome(event.detail, "Bot");
                endChat = true;
            }

            return endChat;
        }

        function processPresenceEvent(event) {

            var endChat = false;
            if (event.status == "joined") {
                agentName = event.from;
                // success("Chatting with" + event.from);
                createMsgIncome("Chatting with " + event.from, "Bot");

    // ==================================================================================
    // ==========================Event Open Popup And Notify Sound=======================
    // ==================================================================================

                var conver = document.getElementById('conversation');
                    conver.style.display = 'none';
                var box_chat = document.getElementById('box_chat');
                    box_chat.style.display ='block';
                    box_chat.style.visibility = 'visible';
                playSound("start");

    // ==================================================================================
    // ==========================End Event Open Popup And Notify Sound===================
    // ==================================================================================

                setUiState(CHAT_UI_STATES.CHATTING);

            } else if (event.status == "left") {
                // info(event.from + " has left");
                createMsgIncome(event.from + " has left", "Bot");
                playSound("end");
                console.log("Save history");
                checkCookie();
                endChat = messageCount == 0;
                setUiState(endChat ? CHAT_UI_STATES.END : CHAT_UI_STATES.TRANSCRIPT_PROMPT);
                socialMinerChat.stopPolling();
                // console.log("RUN SECONDS");
                socialMinerChat.leave();
                // socialMinerChat.delete();
            }

            return endChat;
        }

        function processMessageEvent(event) {
            // var messageId;
            if (!socialminer.utils.isBlank(event.body)) {
                // $("#" + messageId)[0].scrollIntoView();
                // Implement bubble chat
                if (event.from == "me") {
                    createMsgOut(socialminer.utils.trim(event.body));
                } else {
                    createMsgIncome(socialminer.utils.trim(event.body));
                }
                // End
            }
        }

        // function processTypingEvent(event) {
        //     if (event.status == "composing") {
        //         isTyping(event.from);
        //     } else if (event.status == "paused") {
        //         isPaused(event.from);
        //     }
        // }

        function processChatEvents(events) {
            // If an event was received, the updateWaitMessageTimeoutHandle can be cleared, since the event will update
            // the UI.
            //
            if (updateWaitMessageTimeoutHandle != null) {
                clearTimeout(updateWaitMessageTimeoutHandle);
                updateWaitMessageTimeoutHandle = null;
            }

						var i, endChat;
            for (i = 0; i < events.length; i++) {
                socialminer.utils.log("Processing event" + JSON.stringify(events[i]));

                if (events[i].type == "StatusEvent") {
                    endChat = processStatusEvent(events[i]);
                } else if (events[i].type == "PresenceEvent") {
                    endChat = processPresenceEvent(events[i]);
                } else if (events[i].type == "MessageEvent") {
                    processMessageEvent(events[i]);
                } else if (events[i].type == "TypingEvent") {
                    // processTypingEvent(events[i]);
                }

                socialminer.utils.log("Processed event" + JSON.stringify(events[i]));

                if (endChat == true) {
                    socialMinerChat.stopPolling();
                    // console.log("RUN FIRST");
                    // socialMinerChat.leave();
                    // socialMinerChat.delete();
                    setUiState(CHAT_UI_STATES.END);
                    setUiState(CHAT_UI_STATES.ENTERING_INFO);
                    break;
                }
            }
        }

        function startPolling() {
            socialMinerChat.addEventListener(function(events) {
                processChatEvents(events);
            });
            socialMinerChat.startPolling();
        }

        function initiateChat() {
            socialMinerChat = new socialminer.chat();
            socialMinerChat.init(window.location.protocol + "//" + window.location.host, constants.scheme + config.socialminer.host + constants.feedRefURL + config.chat.feedid);
             agentName = null;
             contact = {};
             messageCount = 0;
             updateWaitMessageTimeoutHandle = null;
             lastTypingEvent = null;
            //  clientHisChat = [];

            // Get Info
            getInfo();

            var i = 0;
     
            contact.author = (session.name != undefined && NullorEmptyString(session.name) ? session.name : config.chat.author);
            contact.title = config.chat.title;
            contact.tags = config.chat.tags;

            contact.extensionFields = [];
            contact.extensionFields[i++] = {
                name: "Name",
                value: (session.name != undefined && NullorEmptyString(session.name) ? session.name : config.chat.author)
            };
            // contact.extensionFields[i++] = {
            //     name: "Email",
            //     value: (session.email != undefined && NullorEmptyString(session.email) ? session.email : "Unknown")
            // };
            // contact.extensionFields[i++] = {
            //     name: "Phone",
            //     value: (session.phone != undefined && NullorEmptyString(session.phone) ? session.phone : "Unknown")
            // };
            contact.extensionFields[i++] = {
                name: "ccxqueuetag",
                value:  session.ccxqueuetag != undefined ?  session.ccxqueuetag : 0
            }; //theForm.extensionField_ccxqueuetag.options[theForm.extensionField_ccxqueuetag.selectedIndex].value
            console.log("Run initiate");
            socialMinerChat.initiate(contact,
                function(response) {
                    waitMessageUpdateTimeoutHandle = setTimeout(function() {
                        // success("Please be patient while we connect you with a customer care representative.");
                        // createMsgIncome("Please be patient while we connect you with a customer care representative.", "Bot");

                    }, 5000);

                    // success("Welcome, please wait while we connect you with a customer care representative.");
                    createMsgIncome("Welcome, please wait while we connect you with a customer care representative.", "Bot");
                    setUiState(CHAT_UI_STATES.WAITING_FOR_AGENT);
                    startPolling();
                },
                function(response) {
                    socialminer.utils.log(response);
                });

            // return false;
        }
       function processGetCookie() {
            // Get data TranscriptXml
            socialMinerChat.getTranscriptCookieUrl();
       }
       function deleteSession(){

         socialMinerChat.delete();

       }
        function setUiState(state) {
            switch (state) {
                case CHAT_UI_STATES.ENTERING_INFO:

                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");
                   
					CreateChooseTeam();                  
                  
                    break;

                case CHAT_UI_STATES.WAITING_FOR_AGENT:

                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");

                    if (($(".btn_start").length > 0)){
                        $(".btn_start").remove();
                    }

                    if(($(".info").length > 0)){
                        $(".info").remove();
					}

					if (($(".choose-team").length > 0)){
                         $(".choose-team").remove();
                    }
                    break;

                case CHAT_UI_STATES.CHATTING:

                   // Implement Bubble Chat
                    $(".sendMsg").css("visibility","visible");
                    if (($(".btn_start").length > 0)){
                        $(".btn_start").remove();
                    }
                    if(($(".info").length > 0)){
                        $(".info").remove();
                    }
                    if (($(".choose-team").length > 0)){
                        $(".choose-team").remove();
                    }
                   
                    break;

                case CHAT_UI_STATES.TRANSCRIPT_PROMPT:

                 
                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");
                    if (($(".btn_start").length > 0)){
                        $(".btn_start").remove();
                    }
                    if(($(".info").length > 0)){
                        $(".info").remove();
                    }
                    break;

                case CHAT_UI_STATES.END:
                    // console.log("Run End");
                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");
                    // if (($(".choose-team").length > 0)){
                    //    CreateChooseTeam();
                    // // createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()');
                    // }
                    if(($(".info").length > 0)){
                        $(".info").remove();
                    }
                    break;

                default:

                    socialminer.utils.log("Unexpected state:" + state);
            }
        }

        $(document).ready(function() {

            setUiState(CHAT_UI_STATES.ENTERING_INFO);

            $(".sendMsg").keypress(function(event) {
                var statusMsg, message = $(".sendMsg").val();

                if (!socialminer.utils.isBlank(message)) {
                    if (event.which == 13) {
                        statusMsg = {
                            from: contact.author,
                            status: "paused"
                        };
                        lastTypingEvent = "paused"
                        socialminer.utils.log("Sending:" + message);
                        socialMinerChat.send(message, function() {
                                processMessageEvent({
                                    from: "me",
                                    body: message
                                });
                                socialMinerChat.pushHisclient([{  id : (socialMinerChat.lastEventID()+1).toString(), body : message, from : "me", type: "MessageEvent" }]);
                                socialminer.utils.log("send success");
                            },
                            function() {
                                socialminer.utils.log("send error");
                            });
                        $(".sendMsg").val("");
                        event.preventDefault();
                    } else {
                        if (lastTypingEvent !== "composing") {
                            statusMsg = {
                                from: contact.author,
                                status: "composing"
                            };
                            lastTypingEvent = "composing";
                        }
                    }
                }
            });
        });
    </script>
</head>
<body style="background-color: transparent;" onload="loadSessionChat()">
    <div>
        <div class="box-conversation" style="display: block;" id="conversation">
            <div class="box-header">
              <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/i3-logo.png';?>" alt="">
            </div>
            <div class="box-page">
              <div class="conver-list">
                <div class="gr-avt">
                  <div class="avt-img">
                    <ul>
                      <li><img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v1.png';?>" alt=""></li>
                      <li><img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v2.png';?>" alt=""></li>
                      <li><img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v3.png';?>" alt=""></li>
                    </ul>
                  </div>
                  <span class="conver-name">i3internation</span>
                </div>
                <span class="count-time">2m ago</span>
                <div class="conver-bt">
                  <button type="button" class="new-conver btn-primary btn-lg"> New conversation</button>
                </div>
      
              </div>
              <div class="search-box">
                <p>Find an answer for you</p>
                <div class="input-group">
                  <input type="text" class="form-control search-area" placeholder="Search for...">
                  <span class="input-group-btn">
                    <button class="btn btn-default search-bt glyphicon glyphicon-search" type="button"></button>
                  </span>
                </div><!-- /input-group -->
              </div>
              <div class="wh-new">
                <div class="title-news">What is new in i3</div>
                <div class="new-bl">
                  <div class="new-cnt">
                    <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v1.png';?>" alt="">
                    <div class="cnt">
                      <p>QSR help restaurant improve quality service, reducing waitting time</p>
                      <span>VPC 7.0 is coming soon</span>
                    </div>
                  </div>
                </div>
                <div class="new-bl">
                  <div class="new-cnt">
                    <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v2.png';?>" alt="">
                    <div class="cnt">
                      <p>Do more than video surveillance with a flexible and cost-effective system</p>
                      <span>VPC 7.0 is coming soon</span>
                    </div>
                  </div>
                </div>
                <div class="new-bl">
                  <div class="new-cnt">
                    <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v3.png';?>" alt="">
                    <div class="cnt">
                      <p>Leading video surveillance provider i3 International scores top grade</p>
                      <span>VPC 7.0 is coming soon</span>
                    </div>
                  </div>
                </div>
      
              </div>
            </div>
        </div>
        <div class="box" style="display: block;" id="box_chat">
                <div class="msg-header">
                    <div class="com-name">
                      <img class="back-bt" src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/ctrl-left.svg';?>" alt="">
                      <h5>i3 international</h5>
                    </div>
                    <div class="msg-header-img btn-group" role=" group" aria-label="...">
                      <button type="button" class="per-icon">
                        <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v1.png';?>" alt="">
                        <p>Sunny</p>
                      </button>
                      <button type="button" class="per-icon">
                        <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v2.png';?>" alt="">
                        <p>Bella</p>
                      </button>
                      <button type="button" class="per-icon">
                        <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/v3.png';?>" alt="">
                        <p>Nguyen</p>
                      </button>
                    </div>
                </div>

               
              <div class="cont-ibox">      
                <div class="chat-page s2-chat-page">
                    <div class="msg-inbox s2-msg-inbox">
                        <div class="chat s2-chat" id="chatbox-history">
                            <div class="msg-page s2-msg-page" id="messagesBody">
                              <!-- Content here -->
                            </div>
                            <div class="msg-bottom">
                                <div>
                                    <input type="text" class="form-control input-style sendMsg" placeholder="Write a reply">
                                    <div class="icons btn-group" role="group" aria-label="Basic example">
                              <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/ic_tag_faces_24px.svg';?>" alt=" ">
                            <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/ic_attachment_24px.svg';?>" alt=" ">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ------------------------------- -->

                <!-- ------------------------------- -->
            </div>
            </div>
              <a onclick=on_off() class="float ">
                <i class="my-float ">
                <!-- fa fa-plus  -->
                  <img src="<?php echo base_url() . 'plugins/chat/bubble_chat/img/f-comment.svg';?>" alt=" ">
                </i>
              </a>
            <div id="sound" style="display: none;"></div>
    </div>
              <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.slim.min.js"
                integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo " crossorigin="anonymous ">
              </script> -->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js "
                integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49 " crossorigin="anonymous ">
              </script>
              <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js " integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa " crossorigin="anonymous "></script>
              <script src="<?php echo base_url() . 'plugins/chat/bubble_chat/less/less.min.js';?>"></script>
              
              <script>
                $(document).ready(function () {
                

                $(window).on("blur focus", function(e) {

                    var prevType = $(this).data("prevType");

                    if (prevType != e.type) {   //  reduce double fire issues

                        switch (e.type) {
                            case "blur":
                                console.log("Out page");
                                session.window = false;
                                break;
                            case "focus":
                                console.log("In page");
                                session.window = true;
                                break;
                        }
                    }

                    $(this).data("prevType", e.type);
                })


                  $(".carousel ").carousel();   

                  $(".new-conver").click(function () {

                    $(".box-conversation").hide();

                    $(".box").css({
                         visibility: "visible"
                        });

                    $(".s1-chat-page").show();
                    
                    $(".com-name").click(function () {
                        $(".box").css({
                            visibility: "hidden"
                        });
                    $(".box-conversation").show();

                    });

                });
                    loadElement();
              });
                   function loadElement(){
                    var lengthSales = document.getElementsByClassName('ccx_csq_sales').length;
                    var lengthSupports = document.getElementsByClassName('ccx_csq_support').length;
                    if(lengthSales > 0 && lengthSupports > 0){
                         document.getElementsByClassName('ccx_csq_sales')[lengthSales-1].addEventListener('click',function () {
                          session.ccxqueuetag = this.value;
                          userChoose('ccx_csq_sales');
                        });
                        document.getElementsByClassName('ccx_csq_support')[lengthSupports-1].addEventListener('click',function () {
                          session.ccxqueuetag = this.value;
                          userChoose('ccx_csq_support');
                        });
                    }
                 
                  }
                   function on_off() {
                  
                        var y = document.getElementById('conversation');

                        var x = document.getElementById('box_chat');
                   if (y.style.display ==='none' && x.style.display ==='block') {
                          x.style.visibility = 'hidden';
                          x.style.display ='none';
                          y.style.display ='none';
                        // parent.postMessage('none', config.iframeDOM.parent);
                 } else if(y.style.display ==='none'){
                          y.style.display ='block';
                          x.style.display ='block';
                        // parent.postMessage('block', config.iframeDOM.parent);
                        }
                   else {
                          y.style.display ='none';
                          x.style.display ='none';
                        // parent.postMessage('none',  config.iframeDOM.parent);
                        }
                    }

              </script>      
    </div>

</body>

</html>
