import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;


public class StringUtilities {
	public static String checkForPrefix(String rs, String[] prefixArray){
		int last = -1;
		for(int i = 0; i < prefixArray.length; i++){
			last = rs.lastIndexOf(prefixArray[i]);
			if(last != -1){
				String ss = rs.substring(last + 3, rs.length());
				rs = removeWhiteSpaceAtStringHead(ss);
			}
		}
		return rs;
	}
	
	public static String checkForChar(String rs, String[] prefixArray){
		int index = -1;
		int count = 0;
		for(int i = 0; i < prefixArray.length; i++){
			count = 0;
			char searchChar = prefixArray[i].toCharArray()[0];
			for(int j = 0; j < rs.length(); j++) {
			    char c = rs.charAt(j);
			    if (c == searchChar) {
			        count++;
			    }
			}
			for(int j = 0; j < rs.length(); j++) {
				index = rs.indexOf(searchChar);
				if(index != -1){
					String ss = rs.substring(0,index);
					ss = ss + rs.substring(index + prefixArray[i].length(),rs.length());
					rs = ss.replaceAll("\\s","");
				}
			}
		}
		return rs;
	}
	
	public static String removeWhiteSpaceAtStringHead(String s){
		char c = ' ';
		String rs = null;
		int start = 0;
		for(int i = 0; i < s.length(); i++){
			c = s.charAt(i);
			if(c == ' '){
				start = i + 1;				
			}else{
				break;
			}
		}
		rs = s.substring(start, s.length());
		return rs;
	}
	
	public static String getPrefix(String input, String find){
		String lowerCaseInput = input.toLowerCase();
		int first = lowerCaseInput.indexOf(find.toLowerCase() );
		String s = null;
		if(first > 0){
			s = input.substring(0, first);
		}
		return s;
	}
	
	public static void writeLog(String file, String[] log){
		 try{
			 String fileName = file;//"c://out.txt";
			 File f =new File(fileName);
			 boolean exists = f.exists();
			 FileWriter fstream;
			 if(!exists){
				 //CREATE A NEW TEXT FILE
				 fstream = new FileWriter(f);
			 }else{
				 //ALREADY EXISTS
				 fstream = new FileWriter(fileName,true);
			 }
			 BufferedWriter out = new BufferedWriter(fstream);
			 for(String line : log){
				 out.write(line);
				 out.newLine();
			 }
			 out.close();
	  	}catch (Exception e){//Catch exception if any
		  System.err.println("Error: " + e.getMessage());
	  	}
	}
	
	public static void printArray(String[] array){
		for(String a : array){
			System.out.println(a);
		}
	}
	
	public static File makeFolder(String folder){
		File f=new File(folder);
		if(f.exists()==false){
		    f.mkdirs();
		}
		return f;
	}
}
