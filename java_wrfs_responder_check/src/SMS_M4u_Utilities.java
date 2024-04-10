/*
 * Run simple tests through Java SmsInterface
 *
 * Usage: ApiTester [--secure] [--debug [file] [-c|-r|-s [message]|-t]
 *
 * Originally written by Matthew Kwan - October 2002.
 *
 * Updated by Ethan Smith - 23 July 2009
 *  - Updated for latest version of API
 *  - Added commandline parameters for testing
 * 
 * Updated by Ethan Smith - 18 January 2010
 *  - Fixed identification of prepaid credits
 *  - Added --debug option
 *
 */

//import au.com.m4u.smsapi.*;
import java.io.IOException;

import au.com.m4u.smsapi.MessageStatus;
import au.com.m4u.smsapi.SmsInterface;
import au.com.m4u.smsapi.SmsReply;
import au.com.m4u.smsapi.SmsReplyList;
import au.com.m4u.smsapi.ValidityPeriod;

class SMS_M4u_Utilities {
        // Testing parameters
    private static String m4uUser;// = "Self082";//"WinmaleeRural002";       // messagemedia username
    private static String m4uPass;// = "z7HGcNKW";//"fire000";       // messagemedia password
    //private static String testPhone;// = "+61405319054"; // phone number to receive test messages (start with a + in international format)
    private static SmsInterface si;
    private static SmsReplyList srl = new SmsReplyList();
    
    public SMS_M4u_Utilities(String username, String password){
    	m4uUser = username;
    	m4uPass = password;
	}
        // Open an interface connection.
    public static SmsInterface openConnection (boolean secureMode, boolean debug, String debugFile){
        si = new SmsInterface(1);
        si.useSecureMode(secureMode);

        si.setDebug(debug);

        if (debugFile != ""){
            try {
                si.setDebug(debugFile);
            } catch (IOException e){
                System.err.println ("Could not write to debug output file '" + debugFile + "'");
            }
        }

        if (!si.connect (m4uUser, m4uPass, false)) {
            System.err.println ("Failed to connect");
            return null;
        }

        return si;
    }

        // Test credits remaining.
    private static void testCreditsRemaining(boolean secureMode, boolean debug, String debugFile){
        //SmsInterface    si;

        if ((si = openConnection (secureMode, debug, debugFile)) == null){
            return;
        }
        int cr = si.getCreditsRemaining ();

        if (cr == -1)
            System.out.println ("Account is not a trial");
        else if (cr != -2)
            System.out.println ("Credits remaining = " + cr);
        else {
            System.out.println ("Could not read credit information");
            System.out.println ("Response code = "
                            + si.getResponseCode ());
        }
    }

        // Test sending of messages.
    private static String[] SendMessages (String[] phone, String message, boolean secureMode, boolean debug, String debugFile){
       
    	String[] confirmation = new String[phone.length];
        if ((si = openConnection (secureMode, debug, debugFile)) == null)
            return null;

        for(int i = 0; i < phone.length; i++){
        	si.addMessage (phone[i], message, 0, 0,ValidityPeriod.DEFAULT, false);

        	if (si.sendMessages ()) {
            	confirmation[i] = "SUCCESS : RESPONDER CHECK message sent to " + phone[i];
        	} else {
        		confirmation[i] = "FAIL : RESPONDER CHECK message sent to " + phone[i];
        	}
        }
        return confirmation;
    }

        // Print the contents of a reply.
    private static void printReply (SmsReply sr){
        System.err.println ("Phone = " + sr.getPhoneNumber ());
        System.err.println ("Message = " + sr.getMessage ());
        System.err.println ("ID = " + sr.getMessageID ());
        System.err.println ("WHEN = " + sr.getWhen ());

        String status;

        switch (sr.getDeliveryStatus ()) {
            case MessageStatus.NONE:
            status = "None";
            break;
            case MessageStatus.PENDING:
            status = "Pending";
            break;
            case MessageStatus.DELIVERED:
            status = "Delivered";
            break;
            case MessageStatus.FAILED:
            status = "Failed";
            break;
            default:
            status = "Unknown";
            break;
        }

        System.err.println ("Status = " + status);
    }

        // Test downloading of replies.
    public static SmsReplyList getReplies(){
        if(si == null)
            return null;
        SmsReplyList srl = new SmsReplyList();
        srl = si.checkReplies();

        return srl;
    }
    
    public static String getMessage(SmsReply sr){
    	return sr.getMessage();
    }
    
    public static String[] getTimeRecieved(SmsReply sr){
    	String[] returnArray = {"",""};
    	int when = (int) sr.getWhen() * 1000;//86400000 = 24 hours; 1000 = 1 second
    	int timeCap = 259200000 * 2;
		if(when > timeCap){
			returnArray[0] = "ERROR";
			returnArray[1] = "Date too old";
			System.out.println("Date too old");
		}else{
			String backTime = TimeStampUtilities.convertToTimeStamp(when);
			GetCurrentTimeStamp timeStamp = new GetCurrentTimeStamp();
			String tss = TimeStampUtilities.getTimeStampSeconds(timeStamp);
			String backDate = TimeStampUtilities.getBackDate(tss, backTime);
			returnArray[0] = "ADVICE";
			returnArray[1] = backDate;
		}
		return returnArray;
    }
    
    public static String getPhoneNumber(SmsReply sr){
    	return sr.getPhoneNumber ();
    }
    
    public static String[][] getSMS(SmsReplyList srl){
    	String[][] returnArray = new String[srl.size()][3];
    	String[] message = new String[3];
    	for(int i = 0; i < srl.size(); i++){
    		SmsReply sr = srl.getReply (i);
    		if(getTimeRecieved(sr)[0] == "ADVICE"){
    			message[0] = getMessage(sr);
      			message[1] = getTimeRecieved(sr)[1];
    			message[2] = getPhoneNumber(sr);
    			returnArray[i] = message;
    		}else{
    			message[0] = getTimeRecieved(sr)[0];
    			message[1] = getTimeRecieved(sr)[1];
    			message[2] = getPhoneNumber(sr);
    			returnArray[i] = message;
    		}
    	}
    	return returnArray;
    }
	public static SmsReplyList getSrl() {
		return srl;
	}
	public static void setSrl(SmsReplyList srl) {
		SMS_M4u_Utilities.srl = srl;
	}
    
    //public void closeConnection(){
    //	si.close();
    //}


}
