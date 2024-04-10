//package au.com.m4u.smsapi;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintStream;
import java.net.Authenticator;
import java.net.HttpURLConnection;
import java.net.InetSocketAddress;
import java.net.MalformedURLException;
import java.net.PasswordAuthentication;
import java.net.Proxy;
import java.net.Proxy.Type;
import java.net.URL;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.Date;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;

import au.com.m4u.smsapi.SmsMessage;
import au.com.m4u.smsapi.SmsMessageList;
import au.com.m4u.smsapi.SmsReply;
import au.com.m4u.smsapi.SmsReplyList;

public class SMS_M4u_Interface
{
private int _messageFormat;
private int _responseCode;
private String _responseMessage;
private boolean _useMessageID;
private BufferedReader _input;
private BufferedWriter _output;
private HttpURLConnection _httpConnection;
private ArrayList<String> _serverList;
private SmsMessageList _messageList;
private boolean _secureMode;
private boolean _debug = false;
private FileWriter _debugOutput;
private String _httpProxy = "";
private int _proxyPort = -1;
private String _proxyUsername = "";
private String _proxyPassword = "";

public String[] errorLog = new String[2];

public SMS_M4u_Interface(int paramInt)
{
  this._messageFormat = paramInt;
  this._responseCode = -1;
  this._responseMessage = null;
  this._useMessageID = false;
  this._messageList = new SmsMessageList();
  this._serverList = null;
  this._input = null;
  this._output = null;
  this._httpConnection = null;
  this._secureMode = false;
  //this.errorLog[0] = "true";
  //this.errorLog[1] = "System Good";
}

public void setDebug(boolean paramBoolean)
{
  this._debug = paramBoolean;
}

public void setDebug(String paramString)
  throws IOException
{
  this._debug = true;
  this._debugOutput = new FileWriter(paramString, true);
  Date localDate = new Date();
  this._debugOutput.write("\n--- Output start at " + localDate + " ---\n");
  this._debugOutput.flush();
}

public void setHttpProxy(String paramString1, int paramInt, String paramString2, String paramString3)
{
  this._httpProxy = paramString1;
  this._proxyPort = paramInt;
  this._proxyUsername = paramString2;
  this._proxyPassword = paramString3;
}

public void setHttpProxy(String paramString, int paramInt)
{
  this._httpProxy = paramString;
  this._proxyPort = paramInt;
}

public int getResponseCode()
{
  return this._responseCode;
}

public String getResponseMessage()
{
  return this._responseMessage;
}

public void addMessage(SmsMessage paramSmsMessage)
{
  this._messageList.addMessage(paramSmsMessage);
}

public void addMessage(String paramString1, String paramString2, long paramLong, int paramInt, short paramShort, boolean paramBoolean)
{
  String str = "";

  for (int i = 0; i < paramString1.length(); i++) {
    char c = paramString1.charAt(i);

    if ((c >= '0') && (c <= '9'))
      str = str + c;
    else if ((c == '+') && (str.length() == 0)) {
      str = str + c;
    }
  }
  addMessage(new SmsMessage(str, paramString2, paramLong, paramInt, paramShort, paramBoolean));
}

public void clearMessages()
{
  this._messageList.clear();
}

private boolean openServerConnection(String paramString)
{
  Proxy localProxy = Proxy.NO_PROXY;
  try
  {
    if (this._httpProxy != "") {
      localProxy = new Proxy(Proxy.Type.HTTP, new InetSocketAddress(this._httpProxy, this._proxyPort));

      if ((this._proxyUsername != "") && (this._proxyPassword != ""))
        Authenticator.setDefault(new Authenticator()
        {
          protected PasswordAuthentication getPasswordAuthentication()
          {
            PasswordAuthentication localPasswordAuthentication = new PasswordAuthentication(SMS_M4u_Interface.this._proxyUsername, SMS_M4u_Interface.this._proxyPassword.toCharArray());
            return localPasswordAuthentication;
          }
        });
    }
  }
  catch (IllegalArgumentException localIllegalArgumentException) {
    System.err.println("Could not configure HTTP proxy - " + localIllegalArgumentException.getMessage());
    return false;
  } catch (SecurityException localSecurityException) {
    System.err.println("Could not configure HTTP proxy Authentication - " + localSecurityException.getMessage());
    return false;
  }
  try
  {
    URL localURL;
    if (this._secureMode) {
      localURL = new URL("https://" + paramString);
      this._httpConnection = ((HttpsURLConnection)localURL.openConnection(localProxy));
    } else {
      localURL = new URL("http://" + paramString);
      this._httpConnection = ((HttpURLConnection)localURL.openConnection(localProxy));
    }
    this._httpConnection.setDoOutput(true);
  } catch (MalformedURLException localMalformedURLException) {
    System.err.println("Bad server address '" + paramString + "'");
    return false;
  } catch (IOException localIOException1) {
    this._httpConnection = null;
    System.err.println("Could not open connection to " + paramString + " - " + localIOException1.getMessage());

    return false;
  }

  if (this._secureMode)
  {
    TrustManager[] arrayOfTrustManager = { new X509TrustAllManager() };
    try
    {
      SSLContext localSSLContext = SSLContext.getInstance("TLS");

      localSSLContext.init(null, arrayOfTrustManager, null);
      ((HttpsURLConnection)this._httpConnection).setSSLSocketFactory(localSSLContext.getSocketFactory());
    }
    catch (NoSuchAlgorithmException localNoSuchAlgorithmException) {
      this._httpConnection.disconnect();
      this._httpConnection = null;
      System.err.println("Could not get SSL instance - " + localNoSuchAlgorithmException.getMessage());

      return false;
    } catch (KeyManagementException localKeyManagementException) {
      this._httpConnection.disconnect();
      this._httpConnection = null;
      System.err.println("Could not initialize SSL context - " + localKeyManagementException.getMessage());

      return false;
    }

    ((HttpsURLConnection)this._httpConnection).setHostnameVerifier(new AllHostnameVerifier());
  }

  try
  {
    this._output = new BufferedWriter(new OutputStreamWriter(this._httpConnection.getOutputStream()));

    this._httpConnection.connect();
  } catch (IOException localIOException2) {
    this._httpConnection.disconnect();
    this._httpConnection = null;
    this._output = null;
    
    System.err.println("Could not connect to " + paramString + " - " + localIOException2.getMessage());

    return false;
  }

  return true;
}

private boolean openInputConnection()
{
  if (this._input != null)
    return true;
  try
  {
    this._output.flush();
    this._input = new BufferedReader(new InputStreamReader(this._httpConnection.getInputStream()));
  }
  catch (IOException localIOException1) {
  	errorLog[0] = "false";
  	errorLog[1] = "Could not read from server - " + localIOException1.getMessage();
  	System.err.println("Could not read from server - " + localIOException1.getMessage());

  	return false;
  }
  try
  {
    String str;
    if ((str = this._input.readLine()) == null) {
    	errorLog[0] = "false";
    	errorLog[1] = "Null response from server";
      System.err.println("Null response from server");
      return false;
    }if (!str.startsWith("<HTML><HEAD><TITLE>M4U")) {
      	errorLog[0] = "false";
        	errorLog[1] = "Not an M4U server, should start with <HTML><HEAD><TITLE>M4U";
        	System.err.println("Not an M4U server");
        	return false;
    }

    if ((str = this._input.readLine()) == null) {
    	errorLog[0] = "false";
    	errorLog[1] = "Null body response from server";
      System.err.println("Null body response from server");
      return false;
    }if (!str.startsWith("<BODY>")) {
      	errorLog[0] = "false";
        	errorLog[1] = "Not an M4U body response";
        	System.err.println("Not an M4U body response");
        	return false;
    }
  } catch (IOException localIOException2) {
  	errorLog[0] = "false";
    	errorLog[1] = "Could not read from server - " + localIOException2.getMessage();
    	System.err.println("Could not read from server - " + localIOException2.getMessage());

   	return false;
  }

  return true;
}

public boolean connect(String paramString1, String paramString2, boolean paramBoolean)
{
  if (this._httpConnection != null) {
    return false;
  }
  this._serverList = new ArrayList();
  this._serverList.add("smsmaster.m4u.com.au");
  this._serverList.add("smsmaster1.m4u.com.au");
  this._serverList.add("smsmaster2.m4u.com.au");

  this._useMessageID = paramBoolean;
  
  for (int i = 0; (i < this._serverList.size()) && (!openServerConnection((String)this._serverList.get(i))); i++);
  if (this._httpConnection == null) {
    return false;
  }
  try
  {
    writeToConnection("m4u\r\n");

    if (paramBoolean)
      writeToConnection("USER=" + paramString1 + "#\r\n");
    else {
      writeToConnection("USER=" + paramString1 + "\r\n");
    }
    writeToConnection("PASSWORD=" + paramString2 + "\r\n");
  } catch (IOException localIOException) {
    close();
    System.err.println("Error when writing to the server - " + localIOException.getMessage());

    return false;
  }

  return true;
}

private void writeToConnection(String paramString)
  throws IOException
{
  if (this._debug) {
    try {
      this._debugOutput.write(paramString);
      this._debugOutput.flush();
    }
    catch (IOException localIOException)
    {
    }
    catch (NullPointerException localNullPointerException) {
      System.err.print(paramString);
    }
  }
  this._output.write(paramString);
}

private void close()
{
  if (this._httpConnection == null)
    return;
  try
  {
    if (this._output != null) {
      this._output.flush();
      this._output.close();
    }

    if (this._input != null)
      this._input.close();
  }
  catch (IOException localIOException)
  {
  }
  this._httpConnection.disconnect();
  this._httpConnection = null;
  this._input = null;
  this._output = null;
}

private int readResponseCode()
{
  String str;
  try
  {
    if ((str = this._input.readLine()) == null)
      return 600;
  } catch (IOException localIOException) {
    return 600;
  }
  int i;
  try {
    i = Integer.parseInt(str.substring(0, 3));
    this._responseCode = i;
    this._responseMessage = str.substring(4);
  } catch (NumberFormatException localNumberFormatException) {
    i = 700;
  }

  return i;
}

public boolean changePassword(String paramString)
{
  if (this._httpConnection == null) {
    return false;
  }
  boolean i = true;
  try
  {
    writeToConnection("NEWPASSWORD=" + paramString + "\r\n");
    writeToConnection("MESSAGES\r\n");
    writeToConnection(".\r\n");
  } catch (IOException localIOException) {
    i = false;
  }

  if ((i != false) && (
    (!openInputConnection()) || (readResponseCode() / 100 != 1)))
  {
    i = false;
  }
  close();

  return i;
}

public SmsReplyList checkRepliesAC()
{
  if (this._httpConnection == null){
  	errorLog[0] = "false";
  	errorLog[1] = "http Connection failed.";
  	return null;
  }
  try
  {
    writeToConnection("CHECKREPLY\r\n");
    writeToConnection(".\r\n");
  } catch (IOException localIOException1) {
  	errorLog[0] = "false";
  	errorLog[1] = "Could not write to Connection.";
  	close();
  	return null;
  }
  int i;
  if ((!openInputConnection()) || ((i = readResponseCode()) != 150))
  {
  	//errorLog[0] = "false";
  	//errorLog[1] = "Could not write to Connection.";
  	close();
  	return null;
  }

  SmsReplyList localSmsReplyList = new SmsReplyList();
  try
  {
    String str;
    while (((str = this._input.readLine()) != null) && (!str.startsWith(".")))
    {
      SmsReply localSmsReply;
      if ((localSmsReply = SmsReply.parse(str, this._useMessageID)) != null)
        localSmsReplyList.addReply(localSmsReply);
    }
  }
  catch (IOException localIOException2)
  {
  }
  close();

  return localSmsReplyList;
}

public int getCreditsRemaining()
{
  if (this._httpConnection == null)
    return -2;
  String str1;
  try
  {
    writeToConnection("MESSAGES\r\n");
    writeToConnection(".\r\n");

    if (!openInputConnection()) {
      close();
      return -2;
    }
    str1 = this._input.readLine();
  } catch (IOException localIOException) {
    close();
    return -2;
  }

  close();

  if (str1 == null)
    return -2;
  try
  {
    int i = Integer.parseInt(str1.substring(0, 3));

    this._responseCode = i;
  } catch (NumberFormatException localNumberFormatException1) {
    return -2;
  }

  if (this._responseCode == 100) {
    return -1;
  }
  if (this._responseCode != 120) {
    return -2;
  }

  String str2 = str1.substring(7);
  int j = str2.indexOf(' ');

  if (j < 0)
    return -2;
  try
  {
    return Integer.parseInt(str2.substring(0, j)); } catch (NumberFormatException localNumberFormatException2) {
  }
  return -2;
}

public boolean sendMessages()
{
  if (this._httpConnection == null) {
    return false;
  }
  boolean i = true;
  try
  {
    if (this._messageFormat >= 2)
      writeToConnection("MESSAGES2.0\r\n");
    else {
      writeToConnection("MESSAGES\r\n");
    }
    for (int j = 0; j < this._messageList.size(); j++) {
      SmsMessage localSmsMessage = this._messageList.getMessage(j);

      writeToConnection(localSmsMessage.getMessageID() + " " + localSmsMessage.getPhoneNumber() + " " + localSmsMessage.getDelay() + " ");

      if (this._messageFormat >= 2) {
        writeToConnection(localSmsMessage.getValidityPeriod() + " " + (localSmsMessage.getDeliveryReportRequest() ? '1' : '0') + " ");
      }

      writeToConnection(localSmsMessage.getMessage(true) + "\r\n");
    }
    writeToConnection(".\r\n");
  } catch (IOException localIOException) {
    System.err.println("Failed to write messages to server - " + localIOException.getMessage());

    i = false;
  }

  if ((i != false) && (
    (!openInputConnection()) || (readResponseCode() / 100 != 1)))
  {
    i = false;
  }
  close();

  return i;
}

public static boolean internetConnected()
{
  return internetConnected("http://www.google.com");
}

public static boolean internetConnected(String paramString)
{
  URL localURL;
  try
  {
    localURL = new URL(paramString);
  } catch (MalformedURLException localMalformedURLException) {
    return false;
  }

  HttpURLConnection localHttpURLConnection = null;
  try
  {
    localHttpURLConnection = (HttpURLConnection)localURL.openConnection();
    localHttpURLConnection.connect();
    if (localHttpURLConnection.getResponseCode() / 100 != 2) {
      localHttpURLConnection.disconnect();
      return false;
    }
    InputStream localInputStream = localHttpURLConnection.getInputStream();
  } catch (IOException localIOException) {
    if (localHttpURLConnection != null)
      localHttpURLConnection.disconnect();
    return false;
  }

  localHttpURLConnection.disconnect();

  return true;
}

public String[] getErrorLog()
{
	  return errorLog;
}

public void useSecureMode(boolean paramBoolean)
{
  this._secureMode = paramBoolean;
}
}