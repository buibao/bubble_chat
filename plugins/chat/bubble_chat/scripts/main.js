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
/**
 * Executes on page load
 */
$(document).ready(function() {

	loadConfig();
	loadHisotryChat();
});

/**
 * Loads configs from config/config.json
 */
function loadConfig() {
    //  console.log('Loading config...');
    $.get({
        url: urlConfig,
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
function loadSessionChat(){
	  session.checkSession = false;
	  // Func from UI
      startPolling();
}

function getInfo() {
   
    (session.name != undefined && NullorEmptyString(session.name) ? session.name :  session.name = $("#info_name").val());
    // (session.email != undefined && NullorEmptyString(session.email) ? session.email :  session.email = $("#info_email").val());
    // (session.phone != undefined && NullorEmptyString(session.phone) ? session.phone :  session.phone = $("#info_phone").val());
    console.log(session.name, session.email, session.phone);
    // setTimeout(initiateChatToSocialMiner, config.popup.initdelay);
    // session.startChat = true;
}

function processGetTranScript() {
    // Get data TranscriptXml
  socialminer.getTranscriptCookieUrl();

}

function loadHisotryChat() {
    // check exists history chat
    if (getCookie(config.cookie.name) != undefined) {
        var dtHistory = JSON.parse($.cookie(config.cookie.name));

        // Start Load history
        
        // Get the size of an object
        if (dtHistory.transcript != undefined && dtHistory.transcript.chat != undefined) {
            var arrChat = dtHistory.transcript.chat;
            if(!Array.isArray(arrChat)){
                 // client's chat
                 if (arrChat.name == dtHistory.customer) {
                    createMsgOut(arrChat.msg, convertTimeStamp(arrChat.time));
                } // agent's chat
                else {
                    createMsgIncome(arrChat.msg, null, convertTimeStamp(arrChat.time));
                }
            }else
              {
                    for (var i = 0; i < arrChat.length; i++) {
                        // client's chat
                        if (arrChat[i].name == dtHistory.customer) {
                            createMsgOut(arrChat[i].msg, convertTimeStamp(arrChat[i].time));
                        } // agent's chat
                        else {
                            createMsgIncome(arrChat[i].msg, null, convertTimeStamp(arrChat[i].time));
                        }
                    }
              }
            // createMsgIncome("--------------------History--------------------", "Bot");
        }
        // End Load history

        dtHistory.customer == config.chat.author ?
            (   createMsgIncome(config.bot.welcome_back, "Bot"),
                createMsgIncomeQuestion("input", "text", "form-control", "info_name", "", "info_name", "Enter name", "")
                // CreateChooseTeam()
                // createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()')
            ) :
            (  createMsgIncome(config.bot.welcome_back.concat(", ").concat(dtHistory.customer),"Bot"),
            //    CreateChooseTeam(),
              // createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()'),
               session.name = dtHistory.customer
            );

    } else {
        createMsgIncome(config.bot.welcome, "Bot");
        createMsgIncomeQuestion("input", "text", "form-control", "info_name", "info", "", "Enter name", "","");
        // createMsgIncomeQuestion("input", "email", "form-control", "info_email", "info", "", "Enter email", "","");
        // createMsgIncomeQuestion("input", "numberphone", "form-control", "info_phone", "info", "", "Enter phone", "","");
        // CreateChooseTeam();
        // createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()');
    }



}

function convertTimeStamp(unix_timestamp) {
     var isIE = (navigator.appName.indexOf("Microsoft") != -1) ? 1 : 0;
    var yourday = new Date(parseInt(unix_timestamp));
    normalize = (yourday.getTimezoneOffset() / 60) + 2;
    var tmp = new Date(yourday.getTime());
    // return tmp.toLocaleString([], { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
    return timeAgo(tmp);
    
}

function getCurrentTime() {
    var today = new Date();
    // var time = today.toLocaleString([], { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' }); //today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
    return timeAgo(today);
}

// --------------------------------- Start Set Time -------------------------------------------------

    // now - if no more than five seconds elapsed
    // about a minute ago - in no more than ninety seconds elapsed
    // 24 minutes ago - for anything in the last hour
    // Today at 11:19 - for today
    // Yesterday at 7:32 - for yesterday
    // 15. February at 17:45 - for dates in the current year
    // 23. October 2017. at 0:59 - for anything else

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

function getFormattedDate(date, prefomattedDate = false, hideYear = false) {
  const day = date.getDate();
  const month = MONTH_NAMES[date.getMonth()];
  const year = date.getFullYear();
  const hours = date.getHours();
  let minutes = date.getMinutes();
  let seconds = date.getSeconds();

  if (minutes < 10) {
    // Adding leading zero to minutes
    minutes = `0${ minutes }`;
  }

  if (prefomattedDate) {
    // Today at 10:20
    // Yesterday at 10:20
    if(prefomattedDate == 'Today'){

    return `${ hours }:${ minutes }:${ seconds }`;

    }
    else{
   
        return `${ prefomattedDate } at ${ hours }:${ minutes }:${ seconds }`;
   
    }
  }

  if (hideYear) {
    // 10. January at 10:20
    return `${ day }. ${ month } at ${ hours }:${ minutes }`;
  }

  // 10. January 2017. at 10:20
  return `${ day }. ${ month } ${ year }. at ${ hours }:${ minutes }`;
}

// --- Main function
function timeAgo(dateParam) {
  if (!dateParam) {
    return null;
  }

  const date = typeof dateParam === 'object' ? dateParam : new Date(dateParam);
  const DAY_IN_MS = 86400000; // 24 * 60 * 60 * 1000
  const today = new Date();
  const yesterday = new Date(today - DAY_IN_MS);
  const seconds = Math.round((today - date) / 1000);
  const minutes = Math.round(seconds / 60);
  const isToday = today.toDateString() === date.toDateString();
  const isYesterday = yesterday.toDateString() === date.toDateString();
  const isThisYear = today.getFullYear() === date.getFullYear();

  if (seconds < 5) {
    return 'now';
  } else if (seconds < 60) {
    return `${ seconds }s ago`;
  } else if (seconds < 90) {
    return 'about a minute ago';
  } else if (minutes < 60) {
    return `${ minutes }m ago`;
  } else if (isToday) {
    return getFormattedDate(date, 'Today'); // Today at 10:20
  } else if (isYesterday) {
    return getFormattedDate(date, 'Yesterday'); // Yesterday at 10:20
  } else if (isThisYear) {
    return getFormattedDate(date, false, true); // 10. January at 10:20
  }

  return getFormattedDate(date); // 10. January 2017. at 10:20
}

// --------------------------------- End Set Time -------------------------------------------------



function setCookie(cvalue) {
    var cname = config.cookie.name;
   
    if (getCookie(config.cookie.name) != undefined) {
        var resChat = [];
        var dtHistory = JSON.parse($.cookie(config.cookie.name));
        var value = JSON.parse(cvalue);
        if (dtHistory.transcript != undefined && dtHistory.transcript.chat != undefined) {
            var historychat = dtHistory.transcript.chat;
            Array.isArray(historychat) ?  resChat.push(...historychat) :  resChat.push(historychat);
            console.log("Run dtHistory");

        }
        if(value.transcript != undefined && value.transcript.chat != undefined){
            Array.isArray(value.transcript.chat) ?  resChat.push(...value.transcript.chat) :  resChat.push(value.transcript.chat);
            console.log("Run value");
            value.transcript.chat = resChat;
            cvalue = JSON.stringify(value);
        }else {

            value = { ...value, transcript: { chat: resChat }};
            cvalue = JSON.stringify(value);
        }

    }
    $.cookie(cname, cvalue);
    // from view
    deleteSession();
}

function getCookie(cname) {
    var Cookie = $.cookie(cname);
    return Cookie != undefined ? Cookie : undefined;
}

function checkCookie() {
    var history = getCookie(config.cookie.name);

    if (history != undefined) {
        processGetCookie();
    } else {
        processGetCookie();

    }
}

function createMsgOut(messages, current_time = null) {

    var contentOut = document.createElement('div');
    $(contentOut).addClass("outgoing-chat");

    var contentOutChild = document.createElement('div');
    $(contentOutChild).addClass("outgoing-chat-msg");

    var createP = document.createElement("p");
    $(createP).text(messages);

    var createSpan = document.createElement("span");
    $(createSpan).addClass("time");
    current_time != null ? $(createSpan).text(current_time.toLocaleString([], { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' })) : $(createSpan).text(getCurrentTime());

    contentOutChild.appendChild(createP);
    contentOutChild.appendChild(createSpan);

    contentOut.appendChild(contentOutChild);

    $(contentOut).appendTo(".msg-page");

    var element = document.getElementById("messagesBody");
    element.scrollTop = element.scrollHeight;
}

function createMsgIncome(messages, tyle_ = null, current_time = null) {

    var contentIncome = document.createElement('div');
    $(contentIncome).addClass("received-chats");

    var contentIncomeImg = document.createElement('div');
    $(contentIncomeImg).addClass("received-chats-img");

    var createImg = document.createElement("img");

    tyle_ == null ? $(createImg).attr("src", urlImage + "v1.png") : $(createImg).attr("src", urlImage + config.bot.img);

    contentIncomeImg.appendChild(createImg);


    var contentIncomeMsg = document.createElement('div');
    $(contentIncomeMsg).addClass("received-msg");

    var contentIncomeMsgChild = document.createElement('div');
    $(contentIncomeMsgChild).addClass("received-msg-inbox");

    var createP = document.createElement("p");
    $(createP).text(messages);

    var createSpan = document.createElement("span");
    $(createSpan).addClass("time");
    current_time != null ? $(createSpan).text(current_time.toLocaleString([], { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' })) : $(createSpan).text(getCurrentTime());

    contentIncomeMsgChild.appendChild(createP);
    contentIncomeMsgChild.appendChild(createSpan);

    contentIncomeMsg.appendChild(contentIncomeMsgChild);

    contentIncome.appendChild(contentIncomeImg);
    contentIncome.appendChild(contentIncomeMsg);

    $(contentIncome).appendTo(".msg-page");

    var element = document.getElementById("messagesBody");
    element.scrollTop = element.scrollHeight;
}

function createMsgIncomeQuestion(nameTag_ = null, tyle_ = null, class_ = null, id_ = null, name_ = null, style_ = null, placeholder_ = null, html_ = null, onclick_ = null) {

   
    var contentIncome = document.createElement('div');
    if(tyle_ == "button"){

        $(contentIncome).addClass("received-chats ".concat(id_));

    }else if(name_ == "info"){

        $(contentIncome).addClass("received-chats ".concat(name_));

    }else {

        $(contentIncome).addClass("received-chats");
    }
    var contentIncomeImg = document.createElement('div');
    $(contentIncomeImg).addClass("received-chats-img");

    var createImg = document.createElement("img");
    $(createImg).attr("src", urlImage + config.bot.img);

    contentIncomeImg.appendChild(createImg);


    var contentIncomeMsg = document.createElement('div');
    $(contentIncomeMsg).addClass("received-msg");

    var contentIncomeMsgChild = document.createElement('div');
    $(contentIncomeMsgChild).addClass("received-msg-inbox");


    // create implement for question
    // Start
    var createQuestion = document.createElement(nameTag_);
    $(createQuestion).attr("type", tyle_);
    $(createQuestion).attr("class", class_);
    $(createQuestion).attr("id", id_);
    $(createQuestion).attr("name", name_);
    $(createQuestion).attr("style", style_);
    $(createQuestion).attr("placeholder", placeholder_);
    $(createQuestion).html(html_);
    $(createQuestion).attr("onClick", onclick_);

    // End

    var createSpan = document.createElement("span");
    $(createSpan).addClass("time");
    $(createSpan).text(getCurrentTime());

    // valid input
    // var stringValid = '<div class="valid-feedback feedback-icon"><i class="fa fa-check"></i></div>';
    // var stringInValid = '<div class="invalid-feedback feedback-icon"><i class="fa fa-times"></i></div>';

    contentIncomeMsgChild.appendChild(createQuestion);
    contentIncomeMsgChild.appendChild(createSpan);

    contentIncomeMsg.appendChild(contentIncomeMsgChild);

    contentIncome.appendChild(contentIncomeImg);
    contentIncome.appendChild(contentIncomeMsg);

    $(contentIncome).appendTo(".msg-page");

    var element = document.getElementById("messagesBody");
    element.scrollTop = element.scrollHeight;
}

function CreateChooseTeam(){
    $("#messagesBody").append('<div class="s2-received-chats choose-team"><div class="s2-received-chats-img"><img src="'+urlImage+config.bot.img+'"></div><div class="s2-received-msg"><div class="s2-received-msg-inbox"><p>To point you in the right direction, what i3 team were you hoping to speak with today?</p><span class="time">'+getCurrentTime()+'</span><div class="t-options" style="margin-bottom:3%;font-size: small;"><button class="options ops-1 ccx_csq_sales" id="ccx_csq_sales" name="ccx_csq_sales" value ='+ config.teamOptions.i3Sales +' >Customer Service</button><button class="options ops-2 ccx_csq_support" name="ccx_csq_support" id="ccx_csq_support" value='+ config.teamOptions.i3Supports +' >Tech Support</button></div></div></div></div>');
    loadElement();
    var element = document.getElementById("messagesBody");
    element.scrollTop = element.scrollHeight;
}
function userChoose(option){
    if($(".userChoose").length >0){
        $(".userChoose").remove();
    }
    var stringDiv = '<div class="s2-outgoing-chat userChoose">';
    stringDiv +='<div class="s2-outgoing-chat-msg">';
    stringDiv +=    '<div class="s2-received-msg-inbox">';
    stringDiv +=        '<div class="t-options" style="margin-bottom:3%;font-size: small;">';
    if(option == 'ccx_csq_sales'){
        stringDiv +='<button class="options ops-1 choose-ccx_csq_sales" id="choose-ccx_csq_sales" name="ccx_csq_sales" value='+ config.teamOptions.i3Sales +'>Customer Service</button>';
    }else if('ccx_csq_support'){
        stringDiv +='<button class="options ops-2 choose-ccx_csq_support" name="ccx_csq_support" id="choose-ccx_csq_support" value='+ config.teamOptions.i3Supports +'>Tech Support</button>';    
    }
    stringDiv += '<span class="time">'+getCurrentTime()+'</span>';
    stringDiv +='</div></div></div></div>';
    $("#messagesBody").append(stringDiv);

    if($(".btn_start").length >0){
        $(".btn_start").remove();
    }
    // loadElement();
    createMsgIncomeQuestion("button", "button", "btn btn-default", "btn_start", "", "", "", "Start chat", 'initiateChat()');

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

function NullorEmptyString(msg) {
    return msg.replace(/^\s+|\s+$/g, "").length != 0 ? true : false;
}
