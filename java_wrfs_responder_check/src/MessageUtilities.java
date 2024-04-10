
public class MessageUtilities {
	public static String[] getCodeMessageDetails(String[] message, String[] listOfNames, String[] prefixCheck){
		String[] messageDetails = new String[5];
		
		//String firstName = null;
		//String surName = null;
		String msgName = null;
		String codeText = null;
		String codeNumber = null;
		String codeMessage = null;
		String restOfMessage = "";
		String errorMessage = "";
		
		String[] badChars = {".",","};
		
		//CHECK FOR RE: i.e. prefixCheck
		message[0] = StringUtilities.checkForPrefix(message[0],prefixCheck);
		//CHECK TO SEE IF IT IS A CODE MESSAGE, IF NOT FAIL
		String prefixName = StringUtilities.getPrefix(message[0], "Code");
		System.out.println("prefixName = " + prefixName);

		if(prefixName != null){
			msgName = "";
			String[] splitprefixName = prefixName.split("\\s+");
			System.out.println("splitprefixName.length = " + splitprefixName.length);
			
			int codeIndex = splitprefixName.length;
			//CHECK TO SEE IF THERE IS A FIRST AND SUR NAME, IF NOT FAIL
			if(splitprefixName.length >= 2){
				//SPLIT SUBJECT LINE TEXT INTO AN ARRAY OF SEPERATE WORDS
				String[] splitMessage = message[0].split("\\s+");
				//PREPARE A CONTAINER TO HOLD THE NAME IN THE MESSAGE
				//REMOVE UNWANTED CHAR's FROM splitMessage
				for(int s = 0; s < splitMessage.length; s++){
					System.out.println("splitMessage["+s+"] = " + splitMessage[s]);
					splitMessage[s] = StringUtilities.checkForChar(splitMessage[s],badChars);
					System.out.println("splitMessage["+s+"] = " + splitMessage[s]);
					if(s<codeIndex){
						//COLLECTED THE NAMES IN THE MESSAGE INTO A ARRAY READY FOR TESTING
						msgName = msgName + splitMessage[s];
					}
				}
				System.out.println("msgName = " + msgName);
				//TEST NAMES BY SPLITTING THE NAMES
				//CHECK NAMES (FIRST TWO ITEMS) AGAINST MEMBERS
				System.out.println("MessageUtilities.getCodeMessageDetails :: //CHECK NAMES (FIRST TWO ITEMS) AGAINST MEMBERS///////");

				int codeMessageLength = 0;
				for(int k = 0; k < listOfNames.length; k++){
					//System.out.println("MessageUtilities.getCodeMessageDetails :: //SPLIT ACTIVE MEMBER NAME " + listOfNames[k] + " ///////");
					//String[] splitName = listOfNames[k].split("\\s+");
					String dbName = listOfNames[k].replaceAll("\\s+", "");
					System.out.println("dbName = " + dbName);
					System.out.println("msgName = " + msgName);
					//CHECK FOR AND REMOVE UNWANTED CHAR ON MESSAGE NAME
					
					//CHECK FIRST NAME AND SURNAME OF MESSAGE AGAINST INVIDUAL MEMBERS listOfNames
					if(msgName.compareToIgnoreCase(dbName) == 0){
						msgName = "";
						for(int s = 0; s < codeIndex; s++){
							msgName = msgName + splitMessage[s]+ " ";
						}
						//MATCH FOR CODE
						if(splitMessage[codeIndex].compareToIgnoreCase("code") == 0){
							codeText = splitMessage[codeIndex] ;
							if(splitMessage[codeIndex+1].compareToIgnoreCase("1") == 0 ){
								codeNumber = splitMessage[codeIndex+1];
							}else if(splitMessage[codeIndex+1].compareToIgnoreCase("2") == 0){
								codeNumber = splitMessage[codeIndex+1];
							}else if(splitMessage[codeIndex+1].compareToIgnoreCase("3") == 0){
								codeNumber = splitMessage[codeIndex+1];
							}else{
								codeNumber = null;//codeMessage + null;
								//System.out.println("MessageUtilities.getCodeMessageDetails :: codeNumber = null");
							}//END IF ELSE
						
							//CATER FOR FUTURE ADDITION TO MESSAGE YET TO BE IMPLEMENTED
							if(codeNumber != null){
								//GET REST OF MESSAGE
								for(int m = codeIndex+1; m < splitMessage.length; m++){
									restOfMessage = " " + splitMessage[m];
								}
								codeMessage = msgName + " " + codeText + " " + codeNumber + restOfMessage;
								messageDetails = buildMessageDetailsArray("true", codeNumber, msgName, restOfMessage, errorMessage);
								codeMessageLength = codeMessage.length();
							}else{
								errorMessage = "No Code number in Message or\nCode number is not type after the word code with a space between.\n\n";
								messageDetails = buildMessageDetailsArray("false", codeNumber, msgName, restOfMessage, errorMessage);
								codeMessage = msgName + " " + codeText + " " + codeNumber;
							}//END IF ELSE CODE NUMBER EXISTS
						}else{
							errorMessage = "The word Code or code is not present in the Message or\n not written immediately after your name.\n\n";
							messageDetails = buildMessageDetailsArray("false", codeNumber, msgName, restOfMessage, errorMessage);
						}//END IF ELSE CODE TEXT EXISTS
						break;
					}else{//END IF ELSE
					//if(firstName == null){
						errorMessage = "No Name Match in Active Member List : ";
						msgName = "";
						for(int s = 0; s < codeIndex; s++){
							msgName = msgName + splitMessage[s]+ " ";
						}
						errorMessage = "No Name Match in Active Member List : " + msgName + ".\n Contact the Station Officer.\n\n";
						//System.out.println("MessageUtilities.getCodeMessageDetails :: " + errorMessage);
						messageDetails = buildMessageDetailsArray("false", codeNumber, msgName, restOfMessage, errorMessage);
					}

				}//END FOR
			//}else if(splitprefixName.length > 2){
			//	errorMessage = "Too Many Names : should be First and Second Name Only.\n\n";
				//System.out.println("MessageUtilities.getCodeMessageDetails :: " + errorMessage);
			//	messageDetails = buildMessageDetailsArray("false", codeNumber, firstName + " " + surName, restOfMessage, errorMessage);	
			}else{
				msgName = splitprefixName[0];
				errorMessage = "Only One Name in Message : should be full name as recorded in the Brigades Offical Records.\n\n";
				//System.out.println("MessageUtilities.getCodeMessageDetails :: " + errorMessage);
				messageDetails = buildMessageDetailsArray("false", codeNumber, msgName, restOfMessage, errorMessage);	
			}//END IF : CHECK TO SEE IF THERE IS FIRST AND SECOND NAME
		}else{
			String[] splitMsg = message[0].split("\\s+");
			if(splitMsg[0].compareToIgnoreCase("code") == 0){
				errorMessage = "There is no name in the message or\n the name was typed after the word code.\n\n";
				messageDetails = buildMessageDetailsArray("false", codeNumber, msgName, restOfMessage, errorMessage);
			}else{
				errorMessage = "The word Code or code is not present in the Message or\n not written immediately after your name.\n\n";
				messageDetails = buildMessageDetailsArray("false", codeNumber, "name ignored", restOfMessage, errorMessage);
			}
			
		}//END IF : CHECK FOR CODE MESSAGE

		
		//FLAG FOR GARAGE COLLECTION
		//firstName = null;
		//surName = null;
		codeText = null;
		codeNumber = null;
		codeMessage = null;
		restOfMessage = null;
		errorMessage = null;
		return messageDetails;
	}
	
	private static String[] buildMessageDetailsArray(String success, String codeNum, String name, String restMessage, String errorMessage){
		String[] returnArray = new String[5];
		returnArray[0] = success;
		returnArray[1] = codeNum;
		returnArray[2] = name;
		returnArray[3] = restMessage;
		returnArray[4] = errorMessage;
		return returnArray;
	}
	
	public static String convertToTimeStamp(int value){
    	//1000 = 1 second or in hours/minutes/seconds = 00:00:01
    	//This method only deals with whole seconds, therefore value will be rounded up to 
    	//the nearest 1000
    	//Simplify the calculations for value
    	value = value / 1000;
    	int getHours = 0;
    	int getMinutes = 0;
    	int getSeconds = 0;
    	
    	String getHoursString = "00";
    	String getMinutesString = "00";
    	String getSecondsString = "00";
    	//Test to see if is greater than 24 hours :: 3600,000 (1 hour) x 24 = 86400,000 seconds or 24 hours
    	if(value > 86400){
    		value = value - 86400;
    	}
    	//GET HOURS, MINUTES AND SECONDS	
    	//Test to see if is greater than 60 minutes :: 60,000 (1 minute) x 60 = 3600,000 seconds or 1 hour
    	if(value > 3599){
    		getHours = value / 3600;
    		getMinutes = (value - (getHours * 3600))/60;
    		getSeconds = (value - (getHours * 3600))-(getMinutes*60);
    		if(getMinutes > 60){
    			getHours = getHours + (getMinutes/60);
    			getMinutes = getMinutes - ((getMinutes/60) * 60);
    		}
    		if(getSeconds > 60){
    			getMinutes = getMinutes + (getSeconds/60);
    			getSeconds = getSeconds - ((getSeconds/60) * 60);
    		}
    	//GET MINUTES AND SECONDS ONLY	
    	//Test to see if is greater than 60 seconds :: 1000 x 60 = 60 seconds or 1 minute
    	}else if(value > 59){
    		getMinutes = value / 60 ;
    		getSeconds = value - (getMinutes * 60);
    		if(getSeconds > 60){
    			getMinutes = getMinutes + (getSeconds/60);
    			getSeconds = getSeconds - ((getSeconds/60) * 60);
    		}
    	}else{
    		getSeconds = value;
    	}
    	if(getHours<10){
    		getHoursString = "0" + Integer.toString(getHours);
    	}else{getHoursString = Integer.toString(getHours);};
    	
    	if(getMinutes<10){
    		getMinutesString = "0" + Integer.toString(getMinutes);
    	}else{getMinutesString = Integer.toString(getMinutes);};
    	
    	if(getSeconds<10){
    		getSecondsString = "0" + Integer.toString(getSeconds);
    	}else{getSecondsString = Integer.toString(getSeconds);};
    	
      	return getHoursString + ":" + getMinutesString + ":" + getSecondsString;
    }

	public static String getTimeStampSeconds(GetCurrentTimeStamp timeStamp){
    	String timeStampString = timeStamp.get().toString();
    	timeStampString = timeStampString.substring(0,19);
    	return timeStampString;
    }
}


