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

var restUtil = {
    postChatRequest : function () {
        console.log('POSTing a chat request to SocialMiner ' + config.socialminer.host);
         return $.post({
            url         : constants.scheme + config.socialminer.host + constants.chatURI,
            data        : constructPostPayload(),
            contentType : constants.xmlMIMEType,
            crossDomain : true,
            xhrFields   : { withCredentials : true } // Required to share session cookie while making cross-domain requests
        });
    },

    getChatEvents : function (eventID) {
        console.log('GETting chat events from SocialMiner ' + config.socialminer.host + ' since eventID ' + eventID);
        return $.get({
            url         : constants.scheme + config.socialminer.host + constants.chatURI + constants.chatEventsPathParam + eventID,
            crossDomain : true,
            xhrFields   : { withCredentials: true }  // Required to share session cookie while making cross-domain requests
        });
    },

    putChatMessage : function (message) {
        console.log('PUTting chat message to SocialMiner ' + config.socialminer.host + '. Message = [' + message + ']');
        // silly jQuery does not have a $.put() ?!
        return $.ajax({
            type        : 'PUT',
            url         : constants.scheme + config.socialminer.host + constants.chatURI,
            data        : constructMessagePayload(message),
            contentType : constants.xmlMIMEType,
            crossDomain : true,
            xhrFields   : { withCredentials: true },  // Required to share session cookie while making cross-domain requests
            success: function(xml) {
                        checkCookie();
            }
        });
    },

    deleteChat : function () {
        console.log('DELETEing chat session with SocialMiner ' + config.socialminer.host);
        // silly jQuery does not have a $.delete() ?!
        return $.ajax({
            type        : 'DELETE',
            url         : constants.scheme + config.socialminer.host + constants.chatURI,
            crossDomain : true,
            xhrFields   : { withCredentials: true }  // Required to share session cookie while making cross-domain requests
        });
    },
     getTranscriptDownloadUrl: function()
        {
            // A locale can be specified with the 'locale' parameter.
        return  constants.scheme + config.socialminer.host + constants.chatURI + "/transcript.xml";//"/transcript.pdf?locale=en_ALL";
     }

};

function constructPostPayload () {
    var feedRefURL = constants.scheme + config.socialminer.host + constants.feedRefURL + config.chat.feedid;
    var chatPostPayload =   '<SocialContact>' +
                                '<feedRefURL>' + feedRefURL + '</feedRefURL>' +
                                '<author>' + (session.name != undefined && NullorEmptyString(session.name) ? session.name : config.chat.author)  + '</author>' +
                                '<title>' + config.chat.title  + '</title>' +
                                '<tags>' + config.chat.tags  + '</tags>' +
                                '<extensionFields>' +
                                    '<extensionField>' +
                                        '<name>ccxqueuetag</name>' +
                                        '<value>' + constants.ccx_chat_csq_tag + config.chat.ccx_csq_id  + '</value>' +
                                    '</extensionField>' +
                                    '<extensionField>' +
                                        '<name>h_Name</name>' +
                                        '<value>' + (session.name != undefined && NullorEmptyString(session.name)? session.name : config.chat.author) + '</value>' +
                                    '</extensionField>' +
                                    '<extensionField>' +
                                        '<name>Email</name>' +
                                        '<value>' + (session.email != undefined && NullorEmptyString(session.email) ? session.email : "Unknown") + '</value>' +
                                    '</extensionField>' +
                                    '<extensionField>' +
                                        '<name>Phone</name>' +
                                        '<value>' + (session.phone != undefined && NullorEmptyString(session.phone)? session.phone : "Unknown") + '</value>' +
                                    '</extensionField>' +
                                '</extensionFields>' +
                            '</SocialContact>';

    console.log ('Chat request (POST) : ' + chatPostPayload);
    return chatPostPayload;
}

function constructMessagePayload (message) {
    var chatMessagePayload =    '<Message>' +
                                    '<body>' + message  + '</body>' +
                                '</Message>';

    console.log ('Chat message (PUT) : ' + chatMessagePayload);
    return chatMessagePayload;
}
