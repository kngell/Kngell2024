<?php

/**
 * Generate by StatusCodeGenerator
 */
enum HttpStatusCode: int
{
	/** The server has received the request headers, and the client should proceed to send the request body. */
	case HTTP_CONTINUE = 100;

	/** The requester has asked the server to switch protocols. */
	case HTTP_SWITCHING_PROTOCOLS = 101;

	/** This code indicates that the server has received and is processing the request, but no response is available yet. This prevents the client from timing out and assuming the request was lost. */
	case HTTP_PROCESSING = 102;

	/** Used to return some response headers before final HTTP message. */
	case HTTP_EARLY_HINTS = 103;

	/** The request is OK (this is the standard response for successful HTTP requests). */
	case HTTP_OK = 200;

	/** The request has been fulfilled, and a new resource is created. */
	case HTTP_CREATED = 201;

	/** The request has been accepted for processing, but the processing has not been completed. */
	case HTTP_ACCEPTED = 202;

	/** The request has been successfully processed, but is returning information that may be from another source. */
	case HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

	/** The request has been successfully processed, but is not returning any content. */
	case HTTP_NO_CONTENT = 204;

	/** The request has been successfully processed, but is not returning any content, and requires that the requester reset the document view. */
	case HTTP_RESET_CONTENT = 205;

	/** The server is delivering only part of the resource due to a range header sent by the client. */
	case HTTP_PARTIAL_CONTENT = 206;

	/** The message body that follows is by default an XML message and can contain a number of separate response codes, depending on how many sub-requests were made. */
	case HTTP_MULTI_STATUS = 207;

	/** The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again. */
	case HTTP_ALREADY_REPORTED = 208;

	/** Used as a catch-all error condition for allowing response bodies to flow through Apache when ProxyErrorOverride is enabled. */
	case HTTP_THIS_IS_FINE_APACHE_WEB_SERVER = 218;

	/** The server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance. */
	case HTTP_IM_USED = 226;

	/** A link list. The user can select a link and go to that location. Maximum five addresses. */
	case HTTP_MULTIPLE_CHOICES = 300;

	/** The requested page has moved to a new URL. */
	case HTTP_MOVED_PERMANENTLY = 301;

	/** The requested page has moved temporarily to a new URL. */
	case HTTP_FOUND = 302;

	/** The requested page can be found under a different URL. */
	case HTTP_SEE_OTHER = 303;

	/** Indicates the requested page has not been modified since last requested. */
	case HTTP_NOT_MODIFIED = 304;

	/** No longer used. Originally meant "Subsequent requests should use the specified proxy." */
	case HTTP_SWITCH_PROXY = 306;

	/** The requested page has moved temporarily to a new URL. */
	case HTTP_TEMPORARY_REDIRECT = 307;

	/** Used in the resumable requests proposal to resume aborted PUT or POST requests. */
	case HTTP_RESUME_INCOMPLETE = 308;

	/** The request cannot be fulfilled due to bad syntax. */
	case HTTP_BAD_REQUEST = 400;

	/** The request was a legal request, but the server is refusing to respond to it. For use when authentication is possible but has failed or not yet been provided. */
	case HTTP_UNAUTHORIZED = 401;

	/** Not yet implemented by RFC standards, but reserved for future use. */
	case HTTP_PAYMENT_REQUIRED = 402;

	/** The request was a legal request, but the server is refusing to respond to it. */
	case HTTP_FORBIDDEN = 403;

	/** The requested page could not be found but may be available again in the future. */
	case HTTP_NOT_FOUND = 404;

	/** A request was made of a page using a request method not supported by that page. */
	case HTTP_METHOD_NOT_ALLOWED = 405;

	/** The server can only generate a response that is not accepted by the client. */
	case HTTP_NOT_ACCEPTABLE = 406;

	/** The client must first authenticate itself with the proxy. */
	case HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;

	/** The server timed out waiting for the request. */
	case HTTP_REQUEST_TIMEOUT = 408;

	/** The request could not be completed because of a conflict in the request. */
	case HTTP_CONFLICT = 409;

	/** The requested page is no longer available. */
	case HTTP_GONE = 410;

	/** The "Content-Length" is not defined. The server will not accept the request without it. */
	case HTTP_LENGTH_REQUIRED = 411;

	/** The precondition given in the request evaluated to false by the server. */
	case HTTP_PRECONDITION_FAILED = 412;

	/** The server will not accept the request, because the request entity is too large. */
	case HTTP_REQUEST_ENTITY_TOO_LARGE = 413;

	/** The server will not accept the request, because the URL is too long. Occurs when you convert a POST request to a GET request with a long query information. */
	case HTTP_REQUEST_URI_TOO_LONG = 414;

	/** The server will not accept the request, because the media type is not supported. */
	case HTTP_UNSUPPORTED_MEDIA_TYPE = 415;

	/** The client has asked for a portion of the file, but the server cannot supply that portion. */
	case HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;

	/** The server cannot meet the requirements of the Expect request-header field. */
	case HTTP_EXPECTATION_FAILED = 417;

	/** Any attempt to brew coffee with a teapot should result in the error code "418 I'm a teapot". The resulting entity body MAY be short and stout. */
	case HTTP_IM_A_TEAPOT = 418;

	/** Used by the Laravel Framework when a CSRF Token is missing or expired. */
	case HTTP_PAGE_EXPIRED_LARAVEL_FRAMEWORK = 419;

	/** A deprecated response used by the Spring Framework when a method has failed. */
	case HTTP_METHOD_FAILURE_SPRING_FRAMEWORK = 420;

	/** The request was directed at a server that is not able to produce a response (for example because a connection reuse). */
	case HTTP_MISDIRECTED_REQUEST = 421;

	/** The request was well-formed but was unable to be followed due to semantic errors. */
	case HTTP_UNPROCESSABLE_ENTITY = 422;

	/** The resource that is being accessed is locked. */
	case HTTP_LOCKED = 423;

	/** The request failed due to failure of a previous request (e.g., a PROPPATCH). */
	case HTTP_FAILED_DEPENDENCY = 424;

	/** The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field. */
	case HTTP_UPGRADE_REQUIRED = 426;

	/** The origin server requires the request to be conditional. */
	case HTTP_PRECONDITION_REQUIRED = 428;

	/** The user has sent too many requests in a given amount of time. Intended for use with rate limiting schemes. */
	case HTTP_TOO_MANY_REQUESTS = 429;

	/** The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large. */
	case HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

	/** The client's session has expired and must log in again. (IIS) */
	case HTTP_LOGIN_TIME_OUT = 440;

	/** A non-standard status code used to instruct nginx to close the connection without sending a response to the client, most commonly used to deny malicious or malformed requests. */
	case HTTP_CONNECTION_CLOSED_WITHOUT_RESPONSE = 444;

	/** The server cannot honour the request because the user has not provided the required information. (IIS) */
	case HTTP_RETRY_WITH = 449;

	/** The Microsoft extension code indicated when Windows Parental Controls are turned on and are blocking access to the requested webpage. */
	case HTTP_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;

	/** A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource. */
	case HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

	/** Used by nginx to indicate the client sent too large of a request or header line that is too long. */
	case HTTP_REQUEST_HEADER_TOO_LARGE = 494;

	/** An expansion of the 400 Bad Request response code, used when the client has provided an invalid client certificate. */
	case HTTP_SSL_CERTIFICATE_ERROR = 495;

	/** An expansion of the 400 Bad Request response code, used when a client certificate is required but not provided. */
	case HTTP_SSL_CERTIFICATE_REQUIRED = 496;

	/** An expansion of the 400 Bad Request response code, used when the client has made a HTTP request to a port listening for HTTPS requests. */
	case HTTP_HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;

	/** Returned by ArcGIS for Server. Code 498 indicates an expired or otherwise invalid token. */
	case HTTP_INVALID_TOKEN_ESRI = 498;

	/** A non-standard status code introduced by nginx for the case when a client closes the connection while nginx is processing the request. */
	case HTTP_CLIENT_CLOSED_REQUEST = 499;

	/** An error has occurred in a server side script, a no more specific message is suitable. */
	case HTTP_INTERNAL_SERVER_ERROR = 500;

	/** The server either does not recognize the request method, or it lacks the ability to fulfill the request. */
	case HTTP_NOT_IMPLEMENTED = 501;

	/** The server was acting as a gateway or proxy and received an invalid response from the upstream server. */
	case HTTP_BAD_GATEWAY = 502;

	/** The server is currently unavailable (overloaded or down). */
	case HTTP_SERVICE_UNAVAILABLE = 503;

	/** The server was acting as a gateway or proxy and did not receive a timely response from the upstream server. */
	case HTTP_GATEWAY_TIMEOUT = 504;

	/** The server does not support the HTTP protocol version used in the request. */
	case HTTP_HTTP_VERSION_NOT_SUPPORTED = 505;

	/** Transparent content negotiation for the request results in a circular reference. */
	case HTTP_VARIANT_ALSO_NEGOTIATES = 506;

	/** The server is unable to store the representation needed to complete the request. */
	case HTTP_INSUFFICIENT_STORAGE = 507;

	/** The server detected an infinite loop while processing the request (sent instead of 208 Already Reported). */
	case HTTP_LOOP_DETECTED = 508;

	/** The server has exceeded the bandwidth specified by the server administrator; this is often used by shared hosting providers to limit the bandwidth of customers. */
	case HTTP_BANDWIDTH_LIMIT_EXCEEDED = 509;

	/** Further extensions to the request are required for the server to fulfil it. */
	case HTTP_NOT_EXTENDED = 510;

	/** The client needs to authenticate to gain network access. */
	case HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

	/** The 520 error is used as a "catch-all response for when the origin server returns something unexpected", listing connection resets, large headers, and empty or invalid responses as common triggers. */
	case HTTP_UNKNOWN_ERROR = 520;

	/** The origin server has refused the connection from Cloudflare. */
	case HTTP_WEB_SERVER_IS_DOWN = 521;

	/** Cloudflare could not negotiate a TCP handshake with the origin server. */
	case HTTP_CONNECTION_TIMED_OUT = 522;

	/** Cloudflare could not reach the origin server; for example, if the DNS records for the origin server are incorrect. */
	case HTTP_ORIGIN_IS_UNREACHABLE = 523;

	/** Cloudflare was able to complete a TCP connection to the origin server, but did not receive a timely HTTP response. */
	case HTTP_A_TIMEOUT_OCCURRED = 524;

	/** Cloudflare could not negotiate a SSL/TLS handshake with the origin server. */
	case HTTP_SSL_HANDSHAKE_FAILED = 525;

	/** Used by Cloudflare and Cloud Foundry's gorouter to indicate failure to validate the SSL/TLS certificate that the origin server presented. */
	case HTTP_INVALID_SSL_CERTIFICATE = 526;

	/** Error 527 indicates that the request timed out or failed after the WAN connection had been established. */
	case HTTP_RAILGUN_LISTENER_TO_ORIGIN_ERROR = 527;

	/** Error 530 indicates that the requested host name could not be resolved on the Cloudflare network to an origin server. */
	case HTTP_ORIGIN_DNS_ERROR = 530;

	/** Used by some HTTP proxies to signal a network read timeout behind the proxy to a client in front of the proxy. */
	case HTTP_NETWORK_READ_TIMEOUT_ERROR = 598;
}
