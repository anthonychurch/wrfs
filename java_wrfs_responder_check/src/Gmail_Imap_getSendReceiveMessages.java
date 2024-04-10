import java.io.File;

import javax.mail.Flags;
import javax.mail.Folder;
import javax.mail.MessagingException;
import javax.mail.NoSuchProviderException;
import javax.mail.Message;



public class Gmail_Imap_getSendReceiveMessages {
	private String username, password;
	private Gmail_Imap_Utilities gmail;
	private Folder inbox;
	private int newMessageCount;
	private Message[] newMessages;
	private String inboxFolder = "Inbox";
	private String storeFolder = "storedmessages";
	
	public String[] errorLog = new String[2];
	
	private String[][] subjects = null;
	
	public Gmail_Imap_getSendReceiveMessages(String usrName, String pw){
		username = usrName;
		password = pw;
		gmail = new Gmail_Imap_Utilities();
		gmail.setUserPass(username, password);
		//String[] success = new String[2];
 	}
	
	
	public String[][] getSubjectLines() throws Exception{
		String[][] subjects = null;
		
		if(gmail.connect()){
			try{   
				//gmail.setUserPass("statuswinmaleerfb@gmail.com", "roadrunner01");
				gmail.openFolder(inboxFolder);
				//ENSURE THAT THE STORE MEWSAGES FOLDER IS CREATED BEFORE CHECKING MESSAGES
				String[] foldeExists = gmail.createFolder(storeFolder);
				//System.out.println("Gmail_Imap_getMsgSubjectLine :: foldeExists = " + foldeExists[0]);
				if(Boolean.parseBoolean(foldeExists[0]) == true){
             	  //GET NEW MESSAGES
					newMessages = gmail.getNewMessages();
					newMessageCount = newMessages.length; 
					//System.out.println("Gmail_Imap_getMsgSubjectLine :: newMessages.length = " + newMessages.length);
					if(newMessageCount > 0){
						//gmail.getNewMessages();
						String[][] storeSubjects = new String[newMessageCount][];
            	
						for(int i = 0; i < newMessageCount; i++){
							//System.out.println("Gmail_Imap_getMsgSubjectLine :: newMessages[i] = " + newMessages[i]);
							String[] getSubject = gmail.retreiveSubject(newMessages[i]);
							//System.out.println("Gmail_Imap_getMsgSubjectLine :: getSubject.length = " + getSubject.length);
							storeSubjects[i] = getSubject;
							//System.out.println("Gmail_Imap_getMsgSubjectLine :: storeSubjects[i] = " + storeSubjects[i][0]);
							gmail.setMessageFlag(newMessages[i], new Flags(Flags.Flag.SEEN));
						}
            	
						subjects = storeSubjects;
						gmail.moveMessages(newMessages,inboxFolder,storeFolder);
            
						//FLAG FOR GARBAGE COLLECTION
						storeSubjects = null;
						//getSubject = null;
						foldeExists = null;
					}
				}else{
					String[][] error = {{foldeExists[0],foldeExists[1]}};
					subjects = error;
            		error = null;
				}
            
	    	
			} catch (NoSuchProviderException e) {
				//e.printStackTrace();
				String[][] error = {{"false","No Such provider."}};
				subjects = error;
        		error = null;
	   			//System.exit(1);
			} catch (MessagingException e) {
				//e.printStackTrace();
				String[][] error = {{"false","Java Message Exception."}};
				subjects = error;
        		error = null;
	   			//System.exit(2);
			}
		}else{
			String[][] error = {{"false","Failed to connect to Email Server."}};
			subjects = error;
			error = null;
		}
		
	   	return subjects;
	}
	
	public void close(){
		try {
			gmail.close();
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	public String[] getErrorLog(){
		return errorLog;
	}
	
	public void sendMessage(String recipient, String subject, String body){
		gmail.sendMessage(username, recipient, subject, body);
	}
	
	public void sendMessageAttachment(String sender, String recipient, String subject, String body, String attachment){
		gmail.sendMessageAttachment(username, recipient, subject, body, attachment);
	}
}