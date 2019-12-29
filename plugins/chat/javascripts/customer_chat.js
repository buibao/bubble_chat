/**
 *                           Cisco Systems, Inc.
 *                           Copyright (c) 2014
 *                           All rights reserved.
 *                         Cisco SocialMiner 10.5(1)
 *-------------------------------------------------------------------------
 * The code included in this module is intended to provide guidance to the
 * developer on best practices and usage of the SocialMiner Chat
 * APIs and is not intended for production use “as is”.
 *
 * Cisco's responsibility and liability on this code is limited ONLY to the
 * correctness and accuracy on the usage of the callback interface and the
 * quality of the callback interface itself. Any omissions from this
 * example are not to be considered capabilities that are supported or not
 * supported by the product.
 *
 * For specific capabilites refer to the documentation that accompanies this
 * release and/or request help from the Cisco Developer Network
 * (http://developer.cisco.com) or the Cisco Technical Assistance Center
 */
var socialminer = socialminer || {};

/**
 * This class provides a wrapper around the SocialMiner customer-side chat REST APIs.
 */
socialminer.chat = function()
{
    var chatUrl, feedRefUrl, go = false, pollHandle = null, lastEventId = 0, eventListeners = [],

    /**
     * Process the set of chat events carried in the given eventXmlStr
     *
     * @param eventXmlStr string containing the set of XML chat events
     */
    processEvents = function (eventXmlStr)
    {
        var i, chatEvents, eventId, event, events = [], x2js = new X2JS(), xml = $.parseXML(eventXmlStr);

        chatEvents = $(xml).find("chatEvents").children();

        for ( i = 0 ; i < chatEvents.length ; i++ )
        {
            event = x2js.xml2json(chatEvents[i]);
            event.type = chatEvents[i].nodeName;
            if ( event.body )
            {
                event.body = socialminer.utils.decodeString(event.body);
            }
            events.push(event);

            eventId = parseInt(event.id);
            if (eventId > lastEventId)
            {
                lastEventId = eventId;
            }
        }
        if(lastEventId == 0 && session.checkSession  == false && events.length == 0){
            go = false;
            if ( pollHandle != null )
            {
                clearTimeout(pollHandle);
                pollHandle = null;
			}
			session.checkSession  == undefined;
        }
        notify(events);
    },
    // createChooseTeam = function()
    // {
    // $("#messagesBody").append('<div class="s2-received-chats choose-team"><div class="s2-received-chats-img"><img src="'+urlImage+config.bot.img+'"></div><div class="s2-received-msg"><div class="s2-received-msg-inbox"><p>To point you in the right direction, what i3 team were you hoping to speak with today?</p><span class="time">'+getCurrentTime()+'</span><div class="t-options" style="margin-bottom:3%"><button class="options ops-1 ccx_csq_sales" id="ccx_csq_sales" name="ccx_csq_sales" value ='+ config.teamOptions.i3Sales +' >i3 Sales</button><button class="options ops-2 ccx_csq_support" name="ccx_csq_support" id="ccx_csq_support" value='+ config.teamOptions.i3Supports +' >i3 Support</button></div></div></div></div>');
    // loadElement();
    // var element = document.getElementById("messagesBody");
    // element.scrollTop = element.scrollHeight;

    // },

    /**
     * Notify all event listeners of the given set of events.
     *
     * @param events the list of events to notify listeners of.
     */
    notify = function(events)
    {
        var i;
        if (events.length > 0)
        {
            socialminer.utils.log("Events: " + JSON.stringify(events));
            for (i = 0; i < eventListeners.length; i++)
            {
                eventListeners[i](events);
            }
        }
    },

    /**
     * Poll for new events every 5 seconds.
     * This returns you all chat events including typing events having event type as "TypingEvent".
     * Typing Event can have two states "composing" or "paused".
     */
    poll = function ()
    {
        socialminer.utils.log("Poll eventId: " + lastEventId);
        var dataSend = {
                'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI
        };
        $.ajax(
            {
                type: "GET",
                url        : config.apiUrl.baseUrl + "/polling?urlChat=" + dataSend.urlChat + "&eventID="+lastEventId,
                // url: chatUrl + "?eventid=" + lastEventId,
                cache: false,
                contentType: "application/xml",
                dataType: "text",
                success: function(responseText)
                {
                    processEvents(responseText);
                    if (go == true)
                    {
                        pollHandle = setTimeout(function() { poll(); }, config.chat.pollingInterval);
                        socialminer.utils.log("New eventId: " + lastEventId);
                    }
                },
                error: function(responseText, status)
                {
                    socialminer.utils.log("Error: " + status);
                    notify(
                        [{
                            id: lastEventId + 1,
                            type: "StatusEvent",
                            status: "chat_finished_error",
                            detail: "Server connection temporarily lost. Please try again later."
                        }]);
                }
            });
    };

    return {

        /**
         * Initialize a socialminer.chat object.
         *
         * @param socialMinerBaseUrl the path to SocialMiner
         * @param chatFeedRefUrl the SocialMiner chat feed that captures/supports chats.
         */
        init: function (socialMinerBaseUrl, chatFeedRefUrl)
        {
            console.log("socialMinerBaseUrl: ",socialMinerBaseUrl);
            console.log("chatFeedRefUrl: ",chatFeedRefUrl);
            chatUrl = socialMinerBaseUrl + "/ccp/chat/";
            feedRefUrl = chatFeedRefUrl;
        },

        /**
         * Listen for chat events.
         *
         * @param callback a callback function that takes an array as a parameter. This array will contain combinations
         * of the following objects:
         *   { type: StatusEvent, id: eventId, status: chat_finished_error|chat_issue|chat_ok, detail: eventDetails }
         *   { type: PresenceEvent, id: eventId, from: user, status: joined|left }
         *   { type: MessageEvent, id: eventId, from: user, body: messageText }
         */
        addEventListener: function (callback)
        {
            eventListeners[eventListeners.length] = callback;
        },

        /**
         * Start polling for events.
         */
        startPolling: function ()
        {
            go = true;
            poll();
        },

        /**
         * Stop polling for events.
         */
        stopPolling: function ()
        {
            go = false;
            if ( pollHandle != null )
            {
                clearTimeout(pollHandle);
                pollHandle = null;
            }
        },
        /**
         * Send Typing Event.
         *
         * @param contact a JSON object with the contact details as well as the typing status of the user ( composing or paused ).
         * @param success success callback function. The function is a jQuery ajax() success callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         * @param error error callback function. The function is a jQuery ajax() error callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         */
        sendTypingEvent: function (contact, success, error)
        {
            var typingXml;

            typingXml = "<TypingEvent>";
            typingXml += "<from>" + contact.from + "</from>";
            typingXml += "<status>" + contact.status + "</status>";
            typingXml += "</TypingEvent>";

            $.ajax(
                {
                    type: "PUT",
                    url: chatUrl + "event",
                    cache: false,
                    contentType: "application/xml",
                    data: typingXml,
                    success: success,
                    error: error
                }
            );
        },
        /**
         * Send a chat message.
         *
         * @param message the text of the message to send
         * @param success success callback function. The function is a jQuery ajax() success callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         * @param error error callback function. The function is a jQuery ajax() error callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         */
        send: function (message, success, error)
        {
            // var messageXml;
            // messageXml = "<Message><body>" + socialminer.utils.stripNonValidXMLCharacters(message) + "</body></Message>";
            var dataSend = {
                'message' : socialminer.utils.stripNonValidXMLCharacters(message),
                'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI
            };
            $.ajax(
                {
                   type        : 'PUT',
                   url         : config.apiUrl.baseUrl + "/putChatMessage?urlChat="+dataSend.urlChat+"&message="+dataSend.message,// constants.scheme + config.socialminer.host + constants.chatURI,
                   header:{
                      'Access-Control-Allow-Origin': '*',
                      'Access-Control-Allow-Methods': 'PUT, POST, GET, DELETE, OPTIONS',
                      'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'
                  },
                    // type: "PUT",
                    // url: chatUrl,
                    cache: false,
                    // contentType: "application/xml",
                    // dataType: "xml",
                    // data: messageXml,
                    success: success,
                    error: error
                });
        },
        /**
         *
         * Return the URL used to download a PDF of the chat conversation.
         * @returns {string} URL to the PDF transcript
         */
        getTranscriptDownloadUrl: function()
        {
            // A locale can be specified with the 'locale' parameter.
            //
            return chatUrl + "transcript.pdf?locale=en_ALL";
        },
        getTranscriptCookieUrl: function()
        {
            // A locale can be specified with the 'locale' parameter.
            //
            // return chatUrl + "transcript.xml";
                // A locale can be specified with the 'locale' parameter.
        // return  constants.scheme + config.socialminer.host + constants.chatURI + "/transcript.xml";//"/transcript.pdf?locale=en_ALL";
            var dataSend = {
                    'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI + "/transcript.xml"
            };
            return  $.ajax({
                type        : 'GET',
                url         : config.apiUrl.baseUrl + "/getTranscript?urlChat=" + dataSend.urlChat,
                // crossDomain : true,
                // xhrFields   : { withCredentials: true },  // Required to share session cookie while making cross-domain requests
                header:{
                  'Access-Control-Allow-Origin': '*',
                  'Access-Control-Allow-Methods': 'PUT, POST, GET, DELETE, OPTIONS',
                  'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'

                },
                success: function(xml) {
                              console.log("xml :",xml);
                               // covnert xml to json
                                // console.log(xml.documentElement.outerHTML);
                                 var dataTranscriptJson = $.xml2json(xml);
                                 var dataJsonp = JSON.stringify(dataTranscriptJson);
                                // console.log("history: ",dataJsonp);
                                 if (dataJsonp != "" && dataJsonp != null) {
                                   setCookie(dataJsonp);
                                 }
                              },
                              error: function(xhrReq, textStatus, errorThrown) {
                               console.log("xhrReq:",xhrReq);
                }
            });
        },
        /**
         * Leave the chat room.
         *
         * @param success success callback function. The function is a jQuery ajax() success callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         * @param error error callback function. The function is a jQuery ajax() error callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         */
        leave: function(success, error) // only you leave the chat room, another can continue chat ( multi session chat )
        {
            // $.ajax(
            //     {
            //         type: "PUT",
            //         url: chatUrl + "leaveChat",
            //         cache: false,
            //         success: success,
            //         error: error
            //     }
            // )
                console.log('DELETEing chat session with SocialMiner ' + config.socialminer.host);
                var dataSend = {
                    'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI + "/leaveChat"
                };
                return $.ajax({
                    type        : 'PUT',
                    url         : config.apiUrl.baseUrl + "/leaveChat?urlChat=" + dataSend.urlChat,
                    // crossDomain : true,
                    // xhrFields   : { withCredentials: true },  // Required to share session cookie while making cross-domain requests
                    header:{
                      'Access-Control-Allow-Origin': '*',
                      'Access-Control-Allow-Methods': 'PUT, POST, GET, DELETE, OPTIONS',
                      'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'

                             },
                    cache: false,
                    success: success,
                    error: error
                    // rejectUnauthorized: false,
                });
        },
        delete: function(success, error) // end chat, everybody leave the chat room.
        {
            // $.ajax(
            //     {
            //         type: "DELETE",
            //         url: chatUrl,
            //         cache: false,
            //         success: success,
            //         error: error
            //     }
            // )
            console.log('DELETEing chat session with SocialMiner ' + config.socialminer.host);
            var dataSend = {
                'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI
            };
            return $.ajax({
                type        : 'DELETE',
                url         : config.apiUrl.baseUrl + "/deleteChat?urlChat=" + dataSend.urlChat,
                // crossDomain : true,
                // xhrFields   : { withCredentials: true },  // Required to share session cookie while making cross-domain requests
                header:{
                  'Access-Control-Allow-Origin': '*',
                  'Access-Control-Allow-Methods': 'PUT, POST, GET, DELETE, OPTIONS',
                  'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'

                         },
                // rejectUnauthorized: false,
                cache: false,
                success: success,
                error: error
            });
        },

        /**
         * Initiate a chat session using the chat POST API.
         *
         * @param contact a JSON object with the contact details used to initiate the chat session.
         * @param success success callback function. The function is a jQuery ajax() success callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         * @param error error callback function. The function is a jQuery ajax() error callback. The details can
         *                  be found here: http://api.jquery.com/jquery.ajax/
         */
        initiate: function (contact, success, error)
        {
            console.log("Send chat request.");
            console.log("chatUrl: ",chatUrl);
            var i, contactXml;

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
                'feedRefUrl' : constants.scheme + config.socialminer.host + constants.feedRefURL + config.chat.feedid,
                'urlChat' : constants.scheme + config.socialminer.host + constants.chatURI
          };
            $.ajax(
                {
                    type : 'POST',
                    url  : config.apiUrl.baseUrl + "/createChat?urlChat="+ dataSend.urlChat +"&author="+ dataSend.author + "&title=" + dataSend.title + "&tags=" +dataSend.tags + "&name=" + dataSend.name + "&ccxqueuetag=" +dataSend.ccxqueuetag + "&feedRefUrl=" + dataSend.feedRefUrl,//constants.scheme + config.socialminer.host + constants.chatURI,
                    header:{
                      'Access-Control-Allow-Origin': '*',
                      'Access-Control-Allow-Methods': 'PUT, POST, GET, DELETE, OPTIONS',
                      'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept'
                     },
                    cache: false,
                    // contentType: "application/xml",
                    // data: contactXml,
                    success: success,
                    error: error
                }
            ).done(function(data) {
                console.log("Done: ",data);
            });
            console.log("Done init");
        }
    }
};
