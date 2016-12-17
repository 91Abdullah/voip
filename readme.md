Using ARI
 
-	Stasis App using Laravel Command (Console)
-	API Call to Laravel /api invokes call origination to destination number provided in API Call get request
-	Call then enters into Stasis as it is already subscribed
-	On user answer prompt being played to get user input
-	Prompt keeps on loop until user inputs key 
-	On entering key user’s input is being saved and sent over to client’s api using API call and caller is being prompted with Thank you IVR and call gets hang-up
-	There are 3 use case; 1. Caller press (1) 2. Caller press (2) 3. Caller hang-up without any input (response null sent to client’s api)
