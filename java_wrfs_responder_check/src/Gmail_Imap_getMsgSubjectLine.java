import javax.mail.Flags;
import javax.mail.Folder;
import javax.mail.MessagingException;
import javax.mail.NoSuchProviderException;
import javax.mail.Message;



public class Gmail_Imap_getMsgSubjectLine {
	private String username, password;
	
	private Folder inbox;
	private int newMessageCount;
	private Message[] newMessages;
	private String inboxFolder = "Inbox";
	private String storeFolder = "storedmessages";
	
	private String[][] subjects = null;
	
	public Gmail_Imap_getMsgSubjectLine(String usrName, String pw){
		username = usrName;
		password = pw;
	}
	
	public String[][] getSubjectLines() throws Exception{
		String[][] subjects = null;
		try  
	    {   
	    	Gmail_Imap_Utilities gmail = new Gmail_Imap_Utilities();
	    	//gmail.setUserPass("statuswinmaleerfb@gmail.com", "roadrunner01");
	    	gmail.setUserPass(username, password);
	    	gmail.connect();
            gmail.openFolder(inboxFolder);
            //ENSURE THAT THE STORE MEWSAGES FOLDER IS CREATED BEFORE CHECKING MESSAGES
            String[] foldeExists = gmail.createFolder(storeFolder);
            System.out.println("Gmail_Imap_getMsgSubjectLine :: foldeExists = " + foldeExists[0]);
            if(Boolean.parseBoolean(foldeExists[0]) == true){
                //GET NEW MESSAGES
                newMessages = gmail.getNewMessages();
                newMessageCount = newMessages.length; 
                System.out.println("Gmail_Imap_getMsgSubjectLine :: newMessages.length = " + newMessages.length);
                if(newMessageCount > 0){
                	//gmail.getNewMessages();
                	String[][] storeSubjects = new String[newMessageCount][2];
                	String[] getSubject = new String[3];
            	
            		for(int i = 0; i < newMessageCount; i++){
            			getSubject = gmail.retreiveSubject(newMessages[i]);
            			storeSubjects[i] = getSubject;
            			System.out.println("Gmail_Imap_getMsgSubjectLine :: storeSubjects[i] = " + storeSubjects[i][0]);
            			gmail.setMessageFlag(newMessages[i], new Flags(Flags.Flag.SEEN));
            		}
            	
            		subjects = storeSubjects;
            		gmail.moveMessages(newMessages,inboxFolder,storeFolder);
            
            		//FLAG FOR GARBAGE COLLECTION
            		storeSubjects = null;
            		getSubject = null;
            		foldeExists = null;
                }
            }else{
            	String[] error = {foldeExists[0],foldeExists[1]};
            	String[][] errorArray = new String[1][1];
            	errorArray[0] = error;
            	//subjects = errorArray;
            	error = null;
            	errorArray = null;
            }
            gmail.close();
	    	
	   	} catch (NoSuchProviderException e) {
	   		e.printStackTrace();
			System.exit(1);
	   	} catch (MessagingException e) {
	   		e.printStackTrace();
			System.exit(2);
	   	} finally {
	   		
	   	}
	   	return subjects;
	}
	
	
}
