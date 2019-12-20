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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet/less" type="text/css" href="chat/bubble_chat/less/styles.css" /> 
   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js" type="text/javascript"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script src="chat/bubble_chat/scripts/3rdparty/jquery-xml2json/jquery.xml2json.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script src="chat/javascripts/utils.js"></script>
    <script src="chat/javascripts/customer_chat.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x2js/1.2.0/xml2json.min.js"></script>
    <script src="chat/bubble_chat/scripts/main.js" type="text/javascript"></script>
    <script src="chat/bubble_chat/scripts/constants.js" type="text/javascript"></script>

    <!-- <script src="chat/bubble_chat/scripts/rest-util.js" type="text/javascript"></script> -->

    <script language="JavaScript">
        var socialMinerChat = new socialminer.chat();
        // socialMinerChat.init(window.location.protocol + "//" + window.location.host, "https://hq-socialminer.abc.inc/ccp/feed/100040");
        // //window.location.protocol + "//" + window.location.host
        // // i3-socialminer-1.i3international.com
        // // hq-socialminer.abc.inc
       
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

                setUiState(CHAT_UI_STATES.CHATTING);
            } else if (event.status == "left") {
                // info(event.from + " has left");
                createMsgIncome(event.from + " has left", "Bot");
                console.log("Save history");
                checkCookie();
                endChat = messageCount == 0;
                setUiState(endChat ? CHAT_UI_STATES.END : CHAT_UI_STATES.TRANSCRIPT_PROMPT);
                socialMinerChat.stopPolling();
                socialMinerChat.delete();
            }

            return endChat;
        }

        function processMessageEvent(event) {
            // var messageId;
            if (!socialminer.utils.isBlank(event.body)) {
                // messageId = "message" + messageCount++;
                // $("#messages").append(
                //     "<a id=\"" + messageId + "\" href=\"#\" class=\"list-group-item\">" +
                //     "<h5 class=\"list-group-item-heading\">" + event.from + "</h5>" +
                //     "<p class=\"list-group-item-text\">" + socialminer.utils.trim(event.body) + "</p>" +
                //     "</a>");
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
                    socialMinerChat.leave();
                    socialMinerChat.delete();
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
            socialMinerChat.init(window.location.protocol + "//" + window.location.host + "/index.php", constants.scheme + config.socialminer.host + constants.feedRefURL + config.chat.feedid);
            //window.location.protocol + "//" + window.location.host
            // i3-socialminer-1.i3international.com
            // hq-socialminer.abc.inc
             agentName = null;
             contact = {};
             messageCount = 0;
             updateWaitMessageTimeoutHandle = null;
             lastTypingEvent = null;

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
                value: 0
            }; //theForm.extensionField_ccxqueuetag.options[theForm.extensionField_ccxqueuetag.selectedIndex].value
            console.log("Run initiate");
            var i, contactXml;
            var chatUrl         =  '<?php echo base_url() . 'index.php/welcome/initiate'?>';
            var feedRefUrl  =  'https://hq-socialminer.abc.inc/ccp-webapp/ccp/feed/100040'
            // contactXml = "<SocialContact>";
            // contactXml += "<feedRefURL>" + feedRefUrl + "</feedRefURL>";
            // contactXml += "<author>" + contact.author + "</author>";
            // contactXml += "<title>" + contact.title + "</title>";
            // contactXml += "<tags>" + contact.tags + "</tags>";
            // contactXml += "<extensionFields>";
            // for (i = 0; i < contact.extensionFields.length; i++)
            // {
            //     if ((contact.extensionFields[i].value && (contact.extensionFields[i].value.length > 0)))
            //     {
            //         contactXml += "<extensionField><name>" + contact.extensionFields[i].name + "</name><value>" + contact.extensionFields[i].value + "</value></extensionField>";
            //     }
            // }
            // contactXml += "</extensionFields>";
            // contactXml += "</SocialContact>";

            var dataSend = {
                'author': (session.name != undefined && NullorEmptyString(session.name) ? session.name : config.chat.author),
                'title' : config.chat.title,
                'tags': config.chat.tags,
                'name': (session.name != undefined && NullorEmptyString(session.name) ? session.name : config.chat.author),
                'ccxqueuetag': 0,
                'feedRefUrl' : feedRefUrl
            };
            createChat().done(function(data, textStatus, jqXHR) {
           
            // console.log("data :",data);
            console.log("jqXHR :",jqXHR);
            console.log("textStatus :",textStatus);
                   waitMessageUpdateTimeoutHandle = setTimeout(function() {
                        // success("Please be patient while we connect you with a customer care representative.");
                        createMsgIncome("Please be patient while we connect you with a customer care representative.", "Bot");

                    }, 5000);
                    // success("Welcome, please wait while we connect you with a customer care representative.");
                    createMsgIncome("Welcome, please wait while we connect you with a customer care representative.", "Bot");
                    setUiState(CHAT_UI_STATES.WAITING_FOR_AGENT);
                    startPolling();
                    // socialminer.utils.log(response);
            })
            .fail(function(jqXHR, textStatus) {
                console.log("jqXHR :",jqXHR);
                console.log("textStatus :",textStatus);
                console.error('Failed to initiate chat request! Response status = ' + jqXHR.status);
            });
            function createChat() {
            console.log('POSTing a chat request to SocialMiner ');
            return $.ajax({
                type: "GET",
                    url: chatUrl,
                    contentType: "application/xml",
                    data: dataSend,

            });
          }
            //  initiate(contact).done(function(data, textStatus, jqXHR) {
                    
            //         console.log("data :",data);
            //         console.log("jqXHR :",jqXHR);
            //         console.log("textStatus :",textStatus);

            //     })
            //     .fail(function(jqXHR, textStatus) {
            //         console.log("jqXHR :",jqXHR);
            //         console.log("textStatus :",textStatus);
            //         console.error('Failed to initiate chat request! Response status = ' + jqXHR.status);
            //     });
                // function(response) {
                //     waitMessageUpdateTimeoutHandle = setTimeout(function() {
                //         // success("Please be patient while we connect you with a customer care representative.");
                //         createMsgIncome("Please be patient while we connect you with a customer care representative.", "Bot");

                //     }, 5000);

                //     // success("Welcome, please wait while we connect you with a customer care representative.");
                //     createMsgIncome("Welcome, please wait while we connect you with a customer care representative.", "Bot");
                //     setUiState(CHAT_UI_STATES.WAITING_FOR_AGENT);
                //      startPolling();
                //     console.log(response);
                // },
                // function(response) {
                //     socialminer.utils.log(response);
                // });

          return false;
        }

        
       function processGetCookie() {
            // Get data TranscriptXml
            var dataTranscriptXml =  socialMinerChat.getTranscriptCookieUrl();
            var dataJson = "";
            $.ajax({
                type: "GET",
                url: dataTranscriptXml,
                contentType: "application/xml",
                success: function(xml) {
                    var dataTranscriptJson = $.xml2json(xml.documentElement.outerHTML);
                    var dataJsonp = JSON.stringify(dataTranscriptJson);
                    if (dataJsonp != "" && dataJsonp != null) {
                        setCookie(dataJsonp);
                    }
                },
                error: function(xhrReq, textStatus, errorThrown) {
                    dataCookie = "";
                }
            });

        }

        function setUiState(state) {
            switch (state) {
                case CHAT_UI_STATES.ENTERING_INFO:
                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");
                    if (!($(".btn_start").length > 0)){
                    createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()');
                    }
                   
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

                    // Implement Bubble Chat
                    $(".sendMsg").css("visibility","hidden");
                    if (!($(".btn_start").length > 0)){
                    createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()');
                    }
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

            $("#yesDownload").click(function() {
                $('<iframe id="downloadFrame">').width(1).height(1).css("display", "none")
                    .appendTo("body").attr("src", socialMinerChat.getTranscriptDownloadUrl());
                socialMinerChat.delete();
                setUiState(CHAT_UI_STATES.END);
                setUiState(CHAT_UI_STATES.ENTERING_INFO);
            });

            $("#noDownload").click(function() {
                socialMinerChat.stopPolling();
                socialMinerChat.delete();
                setUiState(CHAT_UI_STATES.END);
                setUiState(CHAT_UI_STATES.ENTERING_INFO);
            });

            $("#leave").click(function() {
                socialMinerChat.leave(function() {
                        socialminer.utils.log("leave success");
                        info("You have left the chat room");
                        createMsgIncome("You have left the chat room.", "Bot");
                        setUiState(CHAT_UI_STATES.TRANSCRIPT_PROMPT);
                    },
                    function(xhr, status) {
                        socialminer.utils.log("leave error:" + status);
                    });
            });

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

<body style="background-color: transparent;">
   
     <div class="box" style="display: block;" id="box_chat">
              <div class="msg-header">
                    <div class="com-name">
                        <h5>i3 international</h5>
                    </div>
                    <div class="msg-header-img btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="per-icon">
                        <img src="chat/bubble_chat/img/v1.png" alt="">
                        <p>Sunny</p>
                      </button>
                        <button type="button" class="per-icon">
                        <img src="chat/bubble_chat/img/v2.png" alt="">
                        <p>Bella</p>
                      </button>
                        <button type="button" class="per-icon">
                        <img src="chat/bubble_chat/img/v3.png" alt="">
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
                                <div>
                                    <input type="text" class="form-control input-style sendMsg" placeholder="Write a reply">
                                    <div class="icons btn-group" role="group" aria-label="Basic example">
                              <img src=" chat/bubble_chat/img/ic_tag_faces_24px.svg " alt=" ">
                            <img src="chat/bubble_chat/img/ic_attachment_24px.svg " alt=" ">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
    </div>
              <a onclick=on_off() class="float ">
                <i class="fa fa-plus my-float ">
                  <img src="chat/bubble_chat/img/f-comment.svg " alt=" ">
                </i>
              </a>
     
              <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.slim.min.js"
                integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo " crossorigin="anonymous ">
              </script> -->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js "
                integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49 " crossorigin="anonymous ">
              </script>
              <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js " integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa " crossorigin="anonymous "></script>
              <script src="chat/bubble_chat/less/less.min.js "></script>
              <script>
                $(document).ready(function () {
                  $(".carousel ").carousel();    
              });
                   function on_off() {
                        var x = document.getElementById('box_chat');
                        if (x.style.display ==='none') {
                        x.style.display ='block';
                        // parent.postMessage('block', config.iframeDOM.parent);
                        } else {
                        x.style.display ='none';
                        // parent.postMessage('none',  config.iframeDOM.parent);
                    }
                }
              </script>      
</body>
</html>