--Obtained from http://code.google.com/p/prosody-modules/source/browse/mod_websocket/mod_websocket.lua
--Author appears to be Ali Sabil <Ali.Sabil@gmail.com>
--Code had no license in attribution in header, emailed Ali 4-17-12 to see if he wanted any since we will have to modify this heavily and probably re-release
--Changes to support latest WebSocket RFC by Fred Kilbourn <fred@fredk.com>

module.host = "*" -- Global module

local logger = require "util.logger";
local log = logger.init("mod_websocket");
local httpserver = require "net.httpserver";
local lxp = require "lxp";
local new_xmpp_stream = require "util.xmppstream".new;
local st = require "util.stanza";
local sm = require "core.sessionmanager";
local sha1 = require "util.hashes".sha1;
local base64 = require "util.encodings".base64;
local binaryXOR = require "util.sasl.scram".binaryXOR;

local sessions = {};
local default_headers = { };


local stream_callbacks = { default_ns = "jabber:client",
		streamopened = sm.streamopened,
		streamclosed = sm.streamclosed,
		handlestanza = core_process_stanza };
function stream_callbacks.error(session, error, data)
	if error == "no-stream" then
		session.log("debug", "Invalid opening stream header");
		session:close("invalid-namespace");
	elseif session.close then
		(session.log or log)("debug", "Client XML parse error: %s", tostring(error));
		session:close("xml-not-well-formed");
	end
end


local function session_reset_stream(session)
	local stream = new_xmpp_stream(session, stream_callbacks);
	session.stream = stream;

	session.notopen = true;

--TODO:	might not be necessary, don't understand yet
--	(added based on http://hg.prosody.im/0.8/rev/ccf417c7b5d4)
--[[
	function session.reset_stream()
		session.notopen = true;
		session.stream:reset()
	end
]]--

	function session.data(conn, data)
--strip 0, 255 out of data string (attempt for basic compliance with old rfc, i think)
--		data, _ = data:gsub("[%z\255]", "")

		log( "debug", "Parsing: %s", data:gsub("%c", ""));

--TODO:	implement frame decoding here
--RFC:	http://tools.ietf.org/html/rfc6455#section-5
log( "debug", "DATA FRAME:" );
log( "debug", "FIRST  BYTE[bits] (FIN[1], RSV1[1], RSV2[1], RSV3[1], Opcode[4]):	" .. string.format( "%02X", data:byte( 1, 1 ) ) );
log( "debug", "SECOND BYTE[bits] (Mask[1], Payload Length[7]):				" .. string.format( "%02X", data:byte( 2, 2 ) ) );
local mask = data:byte( 3, 3 ) * 16777216;
local mask = mask + data:byte( 4, 4 ) * 65536;
local mask = mask + data:byte( 5, 5 ) * 256;
local mask = mask + data:byte( 6, 6 );
log( "debug", "MASK (assumed 7 bit payload): " .. mask );
local x = 7;
repeat
	log( "debug", "VARIABLE PAYLOAD: (MUST STILL DECODE):					" .. string.format( "%02X%02X", data:byte( x, x ), data:byte( x+1, x+1 ) ) );
	x = x+2;
until x >= #data

		local ok, err = stream:feed(data);
		if not ok then
			log("debug", "Received invalid XML (%s) %d bytes: %s", tostring(err), #data,
				data:sub(1, 300):gsub("[\r\n]+", " "):gsub("[%z\1-\31]", "_"):gsub("%c", ""));
			session:close("xml-not-well-formed");
		end
	end
end

local stream_xmlns_attr = {xmlns='urn:ietf:params:xml:ns:xmpp-streams'};
local default_stream_attr = { ["xmlns:stream"] = "http://etherx.jabber.org/streams", xmlns = stream_callbacks.default_ns, version = "1.0", id = "" };
local function session_close(session, reason)
	local log = session.log or log;
	if session.conn then
		if session.notopen then
			session.send("<?xml version='1.0'?>");
			session.send(st.stanza("stream:stream", default_stream_attr):top_tag());
		end
		if reason then
			if type(reason) == "string" then -- assume stream error
				log("info", "Disconnecting client, <stream:error> is: %s", reason);
				session.send(st.stanza("stream:error"):tag(reason, {xmlns = 'urn:ietf:params:xml:ns:xmpp-streams' }));
			elseif type(reason) == "table" then
				if reason.condition then
					local stanza = st.stanza("stream:error"):tag(reason.condition, stream_xmlns_attr):up();
					if reason.text then
						stanza:tag("text", stream_xmlns_attr):text(reason.text):up();
					end
					if reason.extra then
						stanza:add_child(reason.extra);
					end
					log("info", "Disconnecting client, <stream:error> is: %s", tostring(stanza));
					session.send(stanza);
				elseif reason.name then -- a stanza
					log("info", "Disconnecting client, <stream:error> is: %s", tostring(reason));
					session.send(reason);
				end
			end
		end
		session.send("</stream:stream>");
		session.conn:close();
--TODO: figure out how to suppress error that happens here
		if websocket_listener then
			websocket_listener.ondisconnect(session.conn, (reason and (reason.text or reason.condition)) or reason or "session closed");
		end
	end
end


local websocket_listener = { default_mode = "*a" };
function websocket_listener.onincoming(conn, data)
	local session = sessions[conn];
	if not session then
		session = { type = "c2s_unauthed",
			conn = conn,
			reset_stream = session_reset_stream,
			close = session_close,
			dispatch_stanza = stream_callbacks.handlestanza,
			log = logger.init("websocket"),
			secure = conn.ssl };

		function session.send(s)
			conn:write("\00" .. tostring(s) .. "\255");
		end

		sessions[conn] = session;
	end

	session_reset_stream(session);

	if data then
		session.data(conn, data);
	end
end

function websocket_listener.ondisconnect(conn, err)
	local session = sessions[conn];
	if session then
		(session.log or log)("info", "Client disconnected: %s", err);
		sm.destroy_session(session, err);
		sessions[conn] = nil;
		session = nil;
	end
end


function handle_request(method, body, request)
	if request.method ~= "GET" or request.headers["upgrade"] ~= "websocket" or request.headers["connection"] ~= "Upgrade" then
		if request.method == "OPTIONS" then
			return { headers = default_headers, body = "" };
		else
			return "<html><body>You really don't look like a Websocket client to me... what do you want?</body></html>";
		end
	end

	local key = request.headers["sec-websocket-key"];
	if key == nil then
		return "<html><body>You really don't look like an XMPP Websocket client to me... what do you want?</body></html>";
	end
	accept = base64.encode( sha1( key .. "258EAFA5-E914-47DA-95CA-C5AB0DC85B11" ) );

	if not method then
		log("debug", "Request %s suffered error %s", tostring(request.id), body);
		return;
	end

	request.conn:setlistener(websocket_listener);
	request.write("HTTP/1.1 101 Switching Protocols\r\n");
	request.write("Upgrade: websocket\r\n");
	request.write("Connection: Upgrade\r\n");
	request.write("Sec-WebSocket-Accept: " .. accept .. "\r\n");
--TODO: add proper logic for Sec-WebSocket-Protocol and Sec-WebSocket-Extensions
	request.write("\r\n");

	return true;
end

local function setup()
	local ports = module:get_option("websocket_ports") or { 5281 };
	httpserver.new_from_config(ports, handle_request, { base = "xmpp-websocket" });
end
if prosody.start_time then -- already started
	setup();
else
	prosody.events.add_handler("server-started", setup);
end
