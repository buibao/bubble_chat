/**
 * Cisco SocialMiner Pop-up Chat Example
 *
 * Copyright (c) 2016 by Cisco Systems, Inc.
 * All rights reserved.
 *
 * The code included in this example is intended to provide guidance to the
 * developer on best practices and usage of the SocialMiner Chat RESTful
 * APIs and is not intended for production use “as is”.
 *
 * Cisco's responsibility and liability on this code is limited ONLY to the
 * correctness and accuracy on the usage of the Chat RESTful API interface and
 * the quality of the Chat RESTful API interface itself. Any omissions from this
 * example are not to be considered capabilities that are supported or not
 * supported by the product.
 *
 * For specific capabilities refer to the documentation that accompanies the latest
 * Cisco SocialMiner release and/or request help from the Cisco Developer Network
 * (http://developer.cisco.com) or the Cisco Technical Assistance Center
 */

// globals
var config;
var session = {};
var infoUser = {};

/**
 * Executes on page load
 */
$(document).ready(function() {

    loadConfig();
    // auto-initiate a chat to SocialMiner after a fixed duration
    
});

/**
 * Loads configs from config/config.json
 */
function loadConfig() {
    console.log('Loading config...');
    $.get({
        url: 'config/config.json',
        async: false,
        success: function(configJson) {
            console.log('Loaded config: ' + JSON.stringify(configJson));
            config = configJson;
        },
        error: function(err) {
            console.error('Failed to load config. Error = ' + err);
        }
    });
}

function StartChat(){
      console.log("Start Chat");

      setTimeout(initiateChatToSocialMiner, config.popup.initdelay);
      session.startChat = true;
}

/**
 * Initiates (POST) a chat request to SocialMiner
 */
function initiateChatToSocialMiner() {
    console.log("Initiating chat request to SocialMiner " + config.socialminer.host);
    createMsgIncome("Please watting Customer Care join to chat room.");
    restUtil.postChatRequest().done(function(data, textStatus, jqXHR) {
            // update session
            session.scRefURL = jqXHR.getResponseHeader(constants.locationHeader);
            session.latestEventID = 0;
            session.launched = false;
            console.log("Injection of chat successful. SC RefURL = " + session.scRefURL);
            // if (!session.launched) {
            //     // chatbox_ui.launch();    
            //     session.launched = true;
            // }
            // chatbox_ui.showMessage(decodeString("System"), decodeString("Thank you for contacting us. An operator will be with you shortly."));
            // chatbox_ui.showMessage(decodeString("System"), decodeString("Thank you for contacting us. An operator will be with you shortly."));
            // start polling for chat events from SocialMiner
            session.pollerID = setInterval(pollForChatEvents, config.chat.pollingInterval);

        }) 
        .fail(function(jqXHR, textStatus) {
            console.error('Failed to initiate chat request! Response status = ' + jqXHR.status);
        });
}

/**
 * Does one poll for chat events from SocialMiner, parses the set of events
 * received and updates the chat accordingly
 */
 function processGetTranScript(){

    //.css('display', 'none')
  $('<iframe id="downloadFrame">').width(1000).height(250)
                    .appendTo('body').attr('src', restUtil.getTranscriptDownloadUrl());

}

function pollForChatEvents() {

    console.log('Starting to poll for chat events every ' + config.chat.pollingInterval + ' milliseconds...');
    restUtil.getChatEvents(session.latestEventID)
            .done(function(data, textStatus, jqXHR) {
                // parse the XML response
                 console.log('textStatus: ',textStatus);
                 console.log('jqXHR: ',jqXHR);
                var chatEvents = $.xml2json(data);
                console.log('Received chat events: ' + JSON.stringify(chatEvents));

                // process message events
                if (chatEvents && chatEvents.MessageEvent) {
                   
                    processIncomingMessages(chatEvents.MessageEvent);
                }
                if(chatEvents && chatEvents.PresenceEvent){
                   if (!session.launched && chatEvents.PresenceEvent.status == "joined") {
                        createMsgIncome("Customer Care joined to the chat room.");
                        session.launched = true;
                    }
                    processEndChat(chatEvents.PresenceEvent);
                }

              
            }) // When end of Chat inactivity timeout - config from CCX
            .fail(function(messages) {
               console.log("Error :", messages.status);
               if(messages.status == 404){
                    EndChat("left");
               }
            })
}

// Event for a Agent chat with a Customer, if multi chat need fix right here
function EndChat(messages){
     if(messages == "left"){
                    console.log("Need close chat");
                    // chatbox_ui.showMessage(decodeString("System"), decodeString("You are alone in the chat room. Click (X) to close the chat interface."));
                    createMsgIncome("You are alone in the chat room.");
                    // stop polling for chat events
                    clearInterval(session.pollerID);
                    // delete chat session with SocialMiner
                    restUtil.deleteChat().done(new function () {
                        console.log('Chat session terminated successfully.');
                    });
                    session.startChat = undefined;//session.startChat = undefined;
        }
}
// Event for a Agent chat with a Customer, if multi chat need fix right here
function processEndChat(messages) {
     if ($.isArray(messages)) {
        for (var i = 0; i < messages.length; i++) {
           EndChat(messages[i].status);
        }
    } else {
        EndChat(messages.status);
    }   
}

/**
 * Processes incoming MessageEvents
 *
 * @param messages
 */
function processIncomingMessages(messages) {
    if ($.isArray(messages)) {
        for (var i = 0; i < messages.length; i++) {
            // chatbox_ui.showMessage(decodeString(messages[i].from), decodeString(messages[i].body));
             createMsgIncome(messages[i].body);
            session.latestEventID = parseInt(messages[i].id);
        }
    } else {
        // chatbox_ui.showMessage(decodeString(messages.from), decodeString(messages.body));
        createMsgIncome(messages.body);
        session.latestEventID = parseInt(messages.id);
    }
}

function createMsgOut(messages){

      var contentOut = document.createElement('div');
      $(contentOut).addClass("outgoing-chat");

      var contentOutChild = document.createElement('div');
      $(contentOutChild).addClass("outgoing-chat-msg");

      var createP = document.createElement("p");
      $(createP).text(messages);

      var createSpan = document.createElement("span");
      $(createSpan).addClass("time");
      $(createSpan).text("11:09 | Dec 02");

      contentOutChild.appendChild(createP);
      contentOutChild.appendChild(createSpan);

      contentOut.appendChild(contentOutChild);

      $(contentOut).appendTo( ".msg-page" );

      var element = document.getElementById("messagesBody");
      element.scrollTop = element.scrollHeight;
}

function createMsgIncome(messages){

      var contentIncome = document.createElement('div');
      $(contentIncome).addClass("received-chats");

      var contentIncomeImg = document.createElement('div');
      $(contentIncomeImg).addClass("received-chats-img");

      var createImg = document.createElement("img");
      $(createImg).attr("src","img/v1.png");

      contentIncomeImg.appendChild(createImg);


      var contentIncomeMsg = document.createElement('div');
      $(contentIncomeMsg).addClass("received-msg");

      var contentIncomeMsgChild = document.createElement('div');
      $(contentIncomeMsgChild).addClass("received-msg-inbox");

      var createP = document.createElement("p");
      $(createP).text(messages);

      var createSpan = document.createElement("span");
      $(createSpan).addClass("time");
      $(createSpan).text("11:09 | Dec 02");

      contentIncomeMsgChild.appendChild(createP);
      contentIncomeMsgChild.appendChild(createSpan);

      contentIncomeMsg.appendChild(contentIncomeMsgChild);

      contentIncome.appendChild(contentIncomeImg);
      contentIncome.appendChild(contentIncomeMsg);

      $(contentIncome).appendTo( ".msg-page" );

      var element = document.getElementById("messagesBody");
      element.scrollTop = element.scrollHeight;
}

function createMsgIncomeQuestion(nameTag_ = null, tyle_ = null, class_ = null, id_ = null,name_ = null,style_ = null, placeholder_ = null, html_ = null, onclick_ = null){

      var contentIncome = document.createElement('div');
      $(contentIncome).addClass("received-chats");

      var contentIncomeImg = document.createElement('div');
      $(contentIncomeImg).addClass("received-chats-img");

      var createImg = document.createElement("img");
      $(createImg).attr("src","img/v1.png");

      contentIncomeImg.appendChild(createImg);


      var contentIncomeMsg = document.createElement('div');
      $(contentIncomeMsg).addClass("received-msg");

      var contentIncomeMsgChild = document.createElement('div');
      $(contentIncomeMsgChild).addClass("received-msg-inbox");

    
      // create implement for question
      // Start
      var createQuestion = document.createElement(nameTag_);
      $(createQuestion).attr("type",tyle_);
      $(createQuestion).attr("class",class_);
      $(createQuestion).attr("id",id_);
      $(createQuestion).attr("name",name_);
      $(createQuestion).attr("style",style_);
      $(createQuestion).attr("placeholder",placeholder_);
      $(createQuestion).html(html_);
      $(createQuestion).attr("onClick",onclick_);


      // End


      var createSpan = document.createElement("span");
      $(createSpan).addClass("time");
      // $(createSpan).text("11:09 | Dec 02");

      contentIncomeMsgChild.appendChild(createQuestion);
      contentIncomeMsgChild.appendChild(createSpan);

      contentIncomeMsg.appendChild(contentIncomeMsgChild);

      contentIncome.appendChild(contentIncomeImg);
      contentIncome.appendChild(contentIncomeMsg);

      $(contentIncome).appendTo( ".msg-page" );

      var element = document.getElementById("messagesBody");
      element.scrollTop = element.scrollHeight;
}

/**
 * Decode a string carried in a MessageEvent body field.
 *
 * @param str the string to be decoded
 * @returns the decoded string
 */
function decodeString(str) {
    str = decodeURIComponent(str.replace(/\+/g, " "));
    str = str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#x27;').replace(/\//g, '&#x2f;');

    return str;
}