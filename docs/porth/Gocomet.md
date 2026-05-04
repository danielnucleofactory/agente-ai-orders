GoComet | GoTrack | API Documentation


De Viren Sagar
Jul 18, 2025
How to add shipments for tracking on the GoComet platform using API Integration?
Pre-requisites for adding a tracking number on the GoComet platform - 
How to generate an API Token?
Input required for Generating the API Token
Outputs for Success and Failure in Generating the API Token
Creation of Trackings on the GoComet Platform
Input required for the creation of single tracking
Output for Success or Failure Responses for Single Tracking Creation
Input required for the creation of Multiple trackings at once
Output for Success or Failure responses for Multiple Tracking Creations
Additional Parameters for Add Tracking API
Upload the tracking to a specific client team
Assigning a consignee team at the time of tracking creation
Share the tracking with other internal teams
Add POL/POD details with the container number
Adding other additional details
Add Detention and Demurrage Details for Tracking
Auto Detect Feature
Update Multiple Tracking Numbers API
Input required for updating the details
Output for Success or Failure responses received after updating details
Get Live Tracking Data
Inputs required for getting the Live Tracking Data with their format
GET Requests by Date
GET Requests by Time
GET Requests by TimeZone
GET Requests by Tracking IDs
GET Requests by Tracking Numbers
Output of a GET request for Live Tracking for Ocean Shipment
Output of a GET request for Live Tracking for Air Shipment
Output of a GET request for Live Tracking for Road [Surface] Shipment
Output of a GET request for Live Tracking for a Courier Shipment Shipment
Webhooks
Overview
How does GoTrack Webhook help you?
How to set up GoTrack Webhook?
GoComet Tracking Update Webhook
Frequently Asked Questions
How to add shipments for tracking on the GoComet platform using API Integration?
Pre-requisites for adding a tracking number on the GoComet platform - 
API Token generated for the account to which the shipment is to be added for tracking

SCAC (Standard Carrier Alpha Code) code of the carrier

Tracking number - either of

Container number with the factory dispatch date or Port loading and/or destination [For ocean]

It is mandatory to mention POL and/or POD in tracking if you will be tracking with a Container number. This helps GoComet in validating the information coming from the carriers.

In the case of tracking with BL or Booking Number, POL/POD are optional as BLs are always unique for your shipment.

For Port or UNLOCODES, please contact your KAM/CSM

MBL/MAWB number [For Air and Ocean]

Reference number [Internal identifier for your team] [For Air and Ocean]

Tracking type [For Surface tracking]

How to generate an API Token?
Token numbers have to be generated with the GoComet username and password before any other request is sent. 

You can use the username and password of any of the existing users registered on GoComet.

Ensure that this user is an active user, you can not create tokens with a deactivated username

API Tokens will expire every 30 days, ensure that new tokens are generated before the expiry date.

Type of request: POST

URL to be used for generating Token: https://login.gocomet.com/api/v1/integrations/generate-token-number

Input required for Generating the API Token


{
    "email": "gocomet-user-email",
    "password": "password"
}
“email” & “password” = User ID & password of team member registered on the platform

This email ID will be used for the creation of tracking on the platform

Outputs for Success and Failure in Generating the API Token
 Success



{
    "message": "Successfully Generated",
    "token": "secret-token-key",
    "expires_at": "timestamp of 30 days from now",
    "status": "success"
}
Failure

Case 1: Email or Password is incorrect



{
    "error": "Email or Password is incorrect",
    "code": "invalid_user_details",
    "status": "failure"
}
Corrective Action: Confirm email ID and password with your team. 

 Case 2: Unknown Error



{
    "error": "Unknown Error",
    "code": "unknown_error",
    "status": "failure"
}
Corrective Action: Share the error message along with the CUrl request to GoComet. The GoComet team will check and get back with feedback.

Creation of Trackings on the GoComet Platform
Type of request: POST

Input required for the creation of single tracking
URL to be used for Creation of Tracking: https://tracking.gocomet.com/api/v1/integrations/add_tracking_number

The body of the input will include the required details of the tracking to be created


JSON Structure for Ocean





JSON Structure for Air





JSON Structure for Road











JSON Structure for Courier




Output for Success or Failure Responses for Single Tracking Creation
You shall get either the success or failure outputs on the creation of tracking

Success



{
    "message": "Added Successfully",
    "status": "success"
    "tracking_id": "<new gocomet_tracking_id>"
}
Failure

Case 1: Tracking Already exists on the GoComet platform

In case the container number or bl number already exists, the API will return the following output.



{
    "error": "Tracking number already exists with id d3e43f2f-a439-4a97-8a4e-64f03fdf6d78"
}
Corrective Action: No action is to be taken directly as the tracking is already uploaded to the platform. The user team can verify the same.

Case 2: Invalid Token

In case, the token is invalid, the following JSON data will be returned.



{
    "error": "invalid token",
    "code": "invalid_user_details",
    "status": "failure"
}
Corrective Action: Check for the validity of the token, if expired generate a new token.

Case 3: Pol Not Found

In case, the pol_name entered is not found, the following JSON data will be returned.



{
    "error": "POL Not Found. Kindly recheck the port you are searching.",
    "status": "failure"
}
Corrective Action: Check for the correct port code and/or the port mapping

Case 4: Pod Not Found

In case, the pod_name entered is not found, the following JSON data will be returned.



{
    "error": "POD Not Found. Kindly recheck the port you are searching.",
    "status": "failure"
}
Corrective Action: Check for the correct port code and/or the port mapping

Case 5: Shared With Team Not Found

In case, share_with_team_code entered is not found, the following JSON data will be returned.



{
    "error": "tracking[share_with_team_code] is invalid"
}
Corrective Action: Check if the correct team codes are entered

Team codes can be checked by downloading bulk upload Excel from your tracking dashboard and finding the group’s unique id in the Client Internal Teams Helper Sheet, or, Team Management under the User section on the platform, or contacting our support team.

Case 6: Mode Not Found

In case, the mode entered is not found, the following JSON data will be returned.



{
    "error": "'dasd' is not a valid mode"
}
Corrective Action: Check if the mode is entered correctly

Case 7: Tracking Type Not Given (in case of mode Road)

In case tracking_type is not given which is mandatory only in case of road mode, then the following JSON data will be returned.



{
    "error": "tracking[tracking_type] is missing"
}
Corrective Action: In case of Surface/Road tracking, check for the correct tracking type

bl (for the bill of lading type),

bn (for booking number type),

cn (for container type),

po (for purchase order number),

pro (for progressive rotating order type),

ln (number type).

Case 8: Tracking Trial is over

In case your trial account subscription date is over, then the following JSON data will be returned.



{
    "error": "You have exhausted your tracking limit for this trial, to increase this contact us"
}
Corrective Action: You can get in touch with your KAM for restoring the access of the platform

Case 9: You are not part of the Client group team for which the tracking is created

In case your account is not a part f the group for which the tracking is created, then the following JSON data will be returned.
User needs to be part of the team where the tracking is to be created



{
    "error": "You are not a part of the client group you entered."
}
Corrective Action: Check if the correct team code is entered and also if the said user ID that is used for creating a tracking is a part of the team code mentioned

Case 10: Invalid Tracking

In this case, the input values are invalid. It is recommended to check the message here to understand which inputs were invalid. This is a dynamic error.



{
    "error": "<Error Message>",
    "code": "invalid_tracking",
    "status": "failure"
}
Corrective Action: If the integration shows an error message that is not covered in the above scenarios, a dynamic error is shown.

You can rectify this if possible or contact the GoComet team

Case 11: Unknown Error (Response Code: 500)

In this case, the errors are unknown. It is recommended to retry after some time in case of these errors.

If the issue persists, get in touch with the GoComet team



{
    "error": "unknown error",
    "code": "unknown_error",
    "status": "failure"
}
Input required for the creation of Multiple trackings at once
The body of the input will include the required details of the tracking to be created

URL to be used for Creation of Tracking: https://tracking.gocomet.com/api/v1/integrations/add-multiple-tracking



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "reference_no": "january container 001"
    },
    "EGLV0001" : {
      "carrier_code": "EGLV",
      "tracking_number": "EGLV0001",
      "mode": "ocean",
      "reference_no": "january mbl 002"
    }
  }
}
Output for Success or Failure responses for Multiple Tracking Creations
Success



{
    "trackings": "{\"24362563218\":{\"tracking_id\":\"c3d5af07-5a16-4c4a-b996-47be451ca06e\"}}",
    "incorrect_trackings": [
        "24362563217"
    ],
    "existing_trackings": "{}",
    "incorrect_tracking_numbers_error_message": "{\"24362563217\":\"'354' is not a valid mode\"}"
}
 

Failure

Note: 

successfully created trackings will be shown in the “trackings" key.

trackings that could not be created will fall under the "incorrect_trackings" key and info on errors will be shown in the “incorrect_tracking_numbers_error_message" key respectively.

Duplicate tracking info will fall under the "existing_trackings" key.

All errors in add single tracking API are applicable here also, just there can be multiple errors as many as there are trackings requested for creation. 

Case 1: Container Already exists or Incorrect JSON parameter values (Response Code)

In case the container number or bl number already exists, the API will return the following output.

Note: As the example below: only ‘ EGLV0001’ was rejected as it was an existing tracking number but since "96725" was not an incorrect tracking number hence it was created.



{
    "trackings": { "96725": "gocomet-id1" }
    "existing_trackings": ["EGLV0001"],
    "incorrect_trackings": [],
    "incorrect_tracking_numbers_error_message": {}
}
Corrective Action: No action is to be taken directly as the tracking is already uploaded to the platform. The user team can verify the same.

Case 2: Invalid Token

In case, the token is invalid, the following JSON data will be returned.



{
    "error": "invalid token",
    "code": "invalid_user_details",
    "status": "failure"
}
Corrective Action: Check for the validity of the token, if expired generate a new token.

Case 3: Unknown Error (Response Code: 500)

In this case, the errors are unknown. It is recommended to retry after some time in case of these errors.

If the issue persists, get in touch with the GoComet team



{
    "error": "<error message>"
}
Additional Parameters for Add Tracking API
Upload the tracking to a specific client team
Note: This is an extension to the Add Tracking Number API and the Add Multiple Tracking Numbers API

To create tracking for a specific group/team, an additional parameter 
("shipper_identity": "client_group_unique_id") needs to be sent.

You can get the "client_group_unique_id" for the groups/teams of your company by

downloading bulk upload Excel from your tracking dashboard and finding the group’s unique id in the Client Internal Teams Helper Sheet, or

contacting our support team

Add Tracking Number API 



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "tracking_type": "po" # only mandatory in case of road mode
        "reference_no": "january container 001"
        "shipper_identity": "<client_group_unique_id>",
    }
}
Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "tracking_type": "po", # only mandatory in case of road mode
      "reference_no": "january container 001",
      "shipper_identity": "<client_group_unique_id>"
    },
  }
}
Assigning a consignee team at the time of tracking creation
Note: This is an extension to the Add Tracking Number API and the Add Multiple Tracking Numbers API

To assign a consignee group/team, you can send an additional parameter "consignee_identity": "consignee_group_unique_id"

You can get the "consignee_group_unique_id" for the groups/teams of your company by

downloading bulk upload Excel from your tracking dashboard and finding the group’s unique id in the Client Internal Teams Helper Sheet, or

contacting our support team

Add Tracking Number API 



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "tracking_type": "po", # only mandatory in case of road mode
        "reference_no": "january container 001",
        "consignee_identity": "<consignee_group_unique_id>"
    }
}
Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "tracking_type": "po", # only mandatory in case of road mode
      "reference_no": "january container 001",
      "consignee_identity": "<consignee_group_unique_id>"
    },
  }
}
Share the tracking with other internal teams
Note: This is an extension to the Add Tracking Number API, the Add Multiple Tracking Numbers API, and the Update Multiple Tracking API

To share trackings with a specific group/team, an additional parameter 
("share_with_team_code": ["<group_unique_id>"]) needs to be sent. It is an array field and can contain multiple group unique codes

You can get the "<group_unique_id>" for the groups/teams of your company in GoComet by 

downloading bulk upload Excel from your tracking dashboard and finding the group’s unique id in the Client Internal Teams Helper Sheet, or

contacting our support team.

Add Tracking Number API 



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "tracking_type": "po" # only mandatory in case of road mode
        "reference_no": "january container 001"
        "share_with_team_code": ["<group_unique_id_1>", "<group_unique_id_2>"],
    }
}
Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "tracking_type": "po", # only mandatory in case of road mode
      "reference_no": "january container 001",
      "share_with_team_code": ["<group_unique_id_1>", "<group_unique_id_2>"]
    }
  }
}
Update Multiple Tracking API



{
  "token": "secret-token-key",
  "trackings": {
    "gocomet-id1": {
      "share_with_team_code": ["<group_unique_id_1>", "<group_unique_id_2>"]
    }
  }
}
Errors

Case 1. Shared Groups Not Found
In this case, one of the group ids which is passed in the array is wrong



{
    "trackings": "{\"gocomet-id1\":{\"status\":\"failure\",\"message\":\"Please check your shared group unique code.\",\"tracking_number\":null}}"
}
Case 2. Invalid Shared Groups
In this case, one of the group ids may not be from the same company



{
    "trackings": "{\"gocomet-id1\":{\"status\":\"failure\",\"message\":\"Shared groups can be from company groups only.\",\"tracking_number\":null}}"
}
Corrective Action for both cases:

Check if the correct team codes are entered

Team codes can be checked by downloading bulk upload Excel from your tracking dashboard and finding the group’s unique id in the Client Internal Teams Helper Sheet, or, Team Management under the User section on the platform, or contacting our support team.

Add POL/POD details with the container number
Note: This is an extension to the Add Tracking Number API , the Add Multiple Tracking Numbers API and the Update Multiple Tracking API
In case of Update Multiple Tracking API, you can only change pol/pod either when tracking in invalid or has not started (pending)

It is mandatory to mention POL and/or POD in tracking if you will be tracking with a Container number. This helps GoComet in validating the information coming from the carriers. In the case of tracking with BL or Booking Number, POL/POD are optional as BLs are always unique for your shipment.

To add your own POL, POD for containers as an additional parameter 
("pol_name": "<pol_name_value>") and ("pod_name": "<pod_name_value>") needs to be sent, both are optional. 

You can get the "<pol_name>" and "<pod_name>" for the ports in GoComet by 

downloading bulk upload Excel from your tracking dashboard and finding the port name in the seaports sheet or airports sheet. Note that you should enter the exact pol/pod name as mentioned in the sheet, or

contact our support team

Add Tracking Number API 



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "tracking_type": "po", # only mandatory in case of road mode
        "reference_no": "january container 001",
        "pol_name": "<pol_name_value>",
        "pod_name": "<pod_name_value>"
    }
}
Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "tracking_type": "po", # only mandatory in case of road mode
      "reference_no": "january container 001",
      "pol_name": "<pol_name_value>",
      "pod_name": "<pod_name_value>"
    }
  }
}
Update Multiple Tracking API



{
  "token": "secret-token-key",
  "trackings": {
    "gocomet-id1": {
      "pol_name": "<pol_name_value>",
      "pod_name": "<pod_name_value>"
    }
  }
}
Errors 

Case 1. POL/POD not found
In this case, entered pol name is not found in our port database



{
    "trackings": "{\"gocomet-id1\":{\"status\":\"failure\",\"message\":\"POL Not Found. Kindly recheck the port you are searching.\",\"tracking_number\":null}}"
}
Corrective Action: Check for the correct port code and/or the port mapping

Case 2. Invalid Tracking Update(only in case of Update Multiple Tracking API)

This error is shown when POL/POD name is being changed and the shipment is in transit.
You can only change pol/pod either when tracking is marked invalid or has not started(pending)



{
    "trackings": "{\"gocomet-id1\":{\"status\":\"failure\",\"message\":\"POL Not Found. Kindly recheck the port you are searching.\",\"tracking_number\":null}}"
}
Adding other additional details
Note:

dispatch_date is optional in the Add Tracking Number API , the Add Multiple Tracking Numbers API and the Update Multiple Tracking API
whereas
eta and etd are  optional in the Add Tracking Number API and the Add Multiple Tracking Numbers API only

You can also add the following parameters while adding the tracking on the GoComet platform

Factory Dispatch Date: "dispatch_date"

The factory dispatch date is useful for the platform when a container number is added on the platform

Factory dispatch date enables the platform to validate the data for the relevant leg of the shipment as a container may have multiple assigned shipments within a short span of time

Please note that the factory dispatch date needs to be a date before the Gate In date

ETA/ETD : ("eta": "<eta_date>") / ("etd": "<etd_date>")

Adding ETA and/or ETD dates while adding the container/BL on the platform will help the platform fetch the relevant data for the said shipment

Other Data:

You can also add other additional data relevant to the shipment you are tracking

You simply need to add custom columns from the preferences section on the platform

Once the custom columns are added, you can simply add the data as a hash

From the below example - 

“ABCD” = Custom column 1 and “XY” is the data for that custom column

“PQRS” = Custom column 2 and “GFDT” is the data for that custom column

 

Add Tracking Number API 



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "tracking_type": "po" # only mandatory in case of road mode
        "reference_no": "january container 001"
        "dispatch_date": "<dispatch_date>",
        "eta": "<eta_date>",
        "etd": "<etd_date>",
        "other_data": {"ABCD":"XY","PQRS":"GFDT"}
    }
}
Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "96725": {
      "carrier_code": "MAEU",
      "tracking_number": "96725",
      "mode": "ocean",
      "tracking_type": "po", # only mandatory in case of road mode
      "reference_no": "january container 001",
      "dispatch_date": "<dispatch_date>",
      "eta": "<eta_date>",
      "etd": "<etd_date>",
      "other_data": {"ABCD":"XY","PQRS":"GFDT"}
    }
  }
}
Update Multiple Tracking API



{
  "token": "secret-token-key",
  "trackings": {
    "gocomet-id1": {
      "dispatch_date": "<dispatch_date>",
    }
  }
}
Note: 
Parameter Date should be in the "DD/MM/YYYY" format or with Time "02/12/2018T09:00:00" or with Time and zone "02/12/2018T09:00:00+0230"

If you enter other data with space, then we add “_” to replace the space character.

If you enter Product XYZ, we turn it into Product_XYZ

Add Detention and Demurrage Details for Tracking
This feature extends the Add Tracking Number API, and Add Multiple Tracking Numbers API to allow clients to provide detention and demurrage details when creating trackings.

Optional parameter detention_and_demurrage_data can be included with the following fields:



"detention_and_demurrage_data": {
  "detention_free_days": 5,
  "single_detention_charge": 50.0,
  "demurrage_free_days": 7,
  "single_demurrage_charge": 75.0,
  "combined_free_days_at_origin": 3,
  "combined_charges_at_origin": 60.0,
  "combined_free_days_at_destination": 4,
  "combined_charges_at_destination": 80.0,
  "container_size": "40",
  "container_type": "dry",
  "currency": "USD"
}
Field Descriptions

All fields are optional:

detention_free_days: Number of free days for detention (integer)

single_detention_charge: Per-day detention charge after free days (float)

demurrage_free_days: Number of free days for demurrage (integer)

single_demurrage_charge: Per-day demurrage charge after free days (float)

combined_free_days_at_origin: Combined free days at origin port (integer)

combined_charges_at_origin: Combined per-day charges at origin after free days (float)

combined_free_days_at_destination: Combined free days at destination port (integer)

combined_charges_at_destination: Combined per-day charges at destination after free days (float)

container_size: Container size (string, e.g., "20", "40")

container_type: Container type (string, e.g., "dry", "reefer")

currency: Currency code for charges (string, e.g., "USD", "EUR")

Add Tracking Number API



{
    "token": "secret-token-key",
    "tracking": {
        "carrier_code": "MAEU",
        "tracking_number": "967250356",
        "mode": "ocean",
        "reference_no": "january container 001",
        "pol_name": "USLAX",
        "pod_name": "CNSHA",
        "detention_and_demurrage_data": {
            "detention_free_days": 5,
            "single_detention_charge": 50.0,
            "demurrage_free_days": 7,
            "container_size": "40",
            "currency": "USD"
        }
    }
}
Add Multiple Tracking Numbers API



{
    "token": "secret-token-key",
    "trackings": {
        "967250356": {
            "carrier_code": "MAEU",
            "tracking_number": "967250356",
            "mode": "ocean",
            "reference_no": "january container 001",
            "detention_and_demurrage_data": {
                "combined_free_days_at_origin": 3,
                "combined_charges_at_origin": 60.0,
                "currency": "EUR"
            }
        }
    }
}
Update Multiple Tracking API



{
    "token": "secret-token-key",
    "trackings": {
        "gocomet-id1": {
            "detention_and_demurrage_data": {
                "demurrage_free_days": 7,
                "single_demurrage_charge": 75.0,
                "container_type": "reefer"
            }
        }
    }
}
Auto Detect Feature
You can use the Auto-detect feature in case of container numbers if you don't know the carrier. It will automatically try to find the carrier based on number and leg.

You will have to specify one of the following in this case so that the system can properly identify the shipping leg. Sample request for creating an Auto-Detect tracking:

Origin (pol_name) or

Destination port  (pod_name) or

Origin Country (client_origin_country) or

Destination country (client_destination_country) or

Origin Region (client_origin_region) or

Destination region (client_destination_region)

Add Tracking Number API 



{
    "token": "<secret_token>",
    "tracking": {
        "tracking_number": "COSU8039463",
        "mode": "ocean",
        "pol_name": "JNPT (Nhava Sheva), Mumbai, India, INNSA",
        "carrier_code": "",
        "dispatch_date": "<dispatch_date>",
        "auto_detect_carrier": true
    }
}
Note:

Instead of pol_name you can also specify the any of pod_name/client_origin_country/client_destination_country/client_origin_region/client_destination_region. 

Add Multiple Tracking 



{
  "token": "secret-token-key",
  "trackings": {
    "COSU8039463": {
      "tracking_number": "COSU8039463",
        "mode": "ocean",
        "carrier_code": "",
        "pol_name": "JNPT (Nhava Sheva), Mumbai, India, INNSA",
        "dispatch_date": "<dispatch_date>",
        "auto_detect_carrier": true
    }
  }
}
Update Multiple Tracking API



{
  "token": "secret-token-key",
  "trackings": {
    "gocomet-tracking-id1": {
        "mode": "ocean",
        "carrier_code": "",
        "pol_name": "JNPT (Nhava Sheva), Mumbai, India, INNSA",
        "auto_detect_carrier": true
    }
  }
}
Update Multiple Tracking Numbers API
This API helps you to update a few details of the trackings already created on the platform.

Understanding this with an example - 

In adding multiple tracking APIs above we added two tracking numbers 96725 and EGLV0001 and received gocomet-id1 and gocomet-id2 as their unique identifier. Now let’s consider that we want to update the carrier code of 96725 from MAEU to MSCU and archive the tracking EGLV0001 along with updating their reference number. For this, we will use the update multiple tracking numbers API.

Post requests should be made to the URL with the JSON structure given below for updating the tracking number in the GoComet system.

Type of Request: POST

URL to be used for updating a tracking: https://tracking.gocomet.com/api/v1/integrations/update-multiple-tracking

Input required for updating the details
All updateable params are optional 



{
  "token": <token in string>,
  "trackings": {
    "<gocomet-tracking-id1>": {
      "tracking_number": <new tracking number in string>,
      "carrier_code": "MAEU", <scac code in string>,
      "reference_no": "updated tracking 1",
      "pol_name": <string, exact pol name from bulk upload sheet list>
      "pod_name": <string, exact pol name from bulk upload sheet list>
      "dispatch_date": "22/12/2022", <time in string>
      "consignee_identity": <consignee uniq_id>,
      "container_numbers": ["cn1" "cn2"], <array of strings>
      "share_with_team_code": ["unique_id1"], <array_of_string>
      "etd": "12/12/2022", <time in string>
      "vessel_name": <string>,
      "other_data": {"PO number": "412424324", .....}, <hash>
      "bookmarked": true, <boolean - true/false>
      "archived": true, <boolean - true/false>
    },
    "<gocomet-tracking-id1>" : {
      same as above
    }
  }
}

Output for Success or Failure responses received after updating details
Success



{
    "trackings": { 
      "gocomet-id1": {
        "status": "success",
        "message": "Successfully updated tracking"
      }, 
      gocomet-id2": {
        "status": "success",
        "message": "Successfully updated tracking"
      } 
    }
}
 

Failure

Case 1: Invalid tracking Update. 

The carrier code can be changed only if the tracking status is pending or tracking is marked invalid. If you try to update the carrier code of already in transit or completed tracking then you will receive this error. So, in case the carrier code of tracking number 96725 was correct and tracking is in transit then we will get the following output if we try to update the carrier code for it.



{
    "trackings": { 
      "gocomet-id1": {
        "status": "failure",
        "message": "Valid Tracking's Tracking Number and Carrier Cannot be Updated"
      }, 
      gocomet-id2": {
        "status": "success",
        "message": "Successfully updated tracking"
      } 
    }
}
 

Case 2: Invalid Token

In case, the token is invalid, the following JSON data will be returned.



{
    "message": "invalid token",
    "code": "invalid_user_details",
    "status": "failure"
}
Corrective Action: Check for the validity of the token, if expired generate a new token.

Case 3: Unknown Error (Response Code: 500)

In this case, the errors are unknown. It is recommended to retry after some time in case of these errors.



{
    "status": "failure",
    "message": "<error message>"
}
Get Live Tracking Data
Once the trackings are created using the POST request, you can also fetch the details of the shipments tracked on the GoComet platform.

You can refer to the API Mapping Tables below which have key formats and events information in detail - 


Mapping for API Keys


















































Event Mapping for Ocean Shipments









































Event Mapping for Air Shipments





















The status received in the GET API for tracking is defined below - 

Yet to start : Shipments which haven't started their first milestone

Active : Shipments which have started their first milestone.

Action Required : Shipments which don't have the expected data and require User's action.

Invalid ; Shipments which might be wrongly entered or are not currently present in the carrier's database.

Completed : Shipments which have completed their last milestone.

Archived : Shipments which have been automatically archived after 30 days post completion,28 days after invalid or manually archived by the user.

Data Not Found : Data not found on the selected Carrier.

Delayed : Shipments that are delayed where the actual date is greater than the planned date.

Probable Delay : Shipments where the actual date is not yet provided and “Today” is greater than the planned date.

Pending : The shipments are still under process of fetching data from the carrier’s database.

This API provides tracking data updated after the given date. The start_date should be provided as a parameter in the API request. 

The start_date is not the start date of the tracking or the date on which the tracking was created on the GoComet platform, but the date from which you want to fetch the tracking

The API also accepts an end_date but if not provided then the current day date is used which makes it a not mandatory parameter

The end_date will be required when you want to fetch details between a specific date range.

The API also takes tracking_ids as an optional multi-value field (to further filter the live-tracking updates according to specific tracking_ids)

Type of request: GET

URL to be used for GET tracking API: https://tracking.gocomet.com/api/v1/integrations/live-tracking?start_date=<start_date>&end_date=<optional_end_date>&tracking_ids[]=<optional_list/array of tracking id>&token=<secret-token>

Inputs required for getting the Live Tracking Data with their format


{
    "token": <token in string, required>,
    "start_date": <Time in string, required>
    "end_date": <Time in string, optional>
    "tracking_ids": <Array of ids in string, optional" 
}
Note: Parameter Date should be in the "DD/MM/YYYY" format or with Time "02/12/2018T09:00:00" or with Time and zone "02/12/2018T09:00:00+0230"

GET Requests by Date
URL: https://tracking.gocomet.com/api/v1/integrations/live-tracking?start_date=19/02/2019&token=<secret-token>

This will give you all the tracking details added and updated on and after the mentioned date -19th February 2019 in this example

GET Requests by Time
URL: https://tracking.gocomet.com/api/v1/integrations/live-tracking?start_date=19/02/2019T09:00:21&token=<secret-token>

This will give you all the tracking details added and updated on and after the mentioned date and time - 19th February 2019 09:00:21 hrs in this example

GET Requests by TimeZone
URL: https://tracking.gocomet.com/api/v1/integrations/live-tracking?start_date=19/02/2019T09:00:21%2B0530&token=<secret-token>

This will give you all the tracking details added and updated on and after the mentioned date and time with the time zone - 19th February 2019 09:00:21 hrs with +05:30 timezone in this example

GET Requests by Tracking IDs
URL: https://tracking.gocomet.com/api/v1/integrations/live-tracking?tracking_ids[]=gocomet_tracking_id1&tracking_ids[]=gocomet_tracking_id2&token=<secrettoken>

This will give you all the tracking details updated for the tracking IDs mentioned

GET Requests by Tracking Numbers
URL: https://tracking.gocomet.com/api/v1/integrations/live-tracking?tracking_numbers[]=gocomet_tracking_number1&tracking_numbers[]=gocomet_tracking_number2&token=<secrettoken>

This will give you all the tracking details updated for the tracking Numbers mentioned

Note: 

start_date=19/02/2019T09:00:21+0530 is wrong because the "+" (plus) character is not excepted in the URL hence it needs to be encoded (https://www.w3.org/Addressing/URL/4_URI_Recommentations.html )

Since tracking_ids is an “array/list“ field the multiple ids need to be sent with “[]=” before each id like:
https://tracking.gocomet.com/api/v1/integrations/live-tracking?start_date=01/01/2020&tracking_ids[]=gocomet_tracking_id1&tracking_ids[]=gocomet_tracking_id2&token=<secrettoken>


Example of a GET request for Ocean Shipment



Example of a GET request for Air Shipment



Example of a GET request for Road Shipment



Example of a GET request for Courier Shipment


Webhooks
Overview
Webhooks are a mechanism used in web development to facilitate real-time communication and data exchange between different web applications or services. They are a way for one application to send automatic, real-time updates or notifications to another application whenever a specific event or trigger occurs.

How does GoTrack Webhook help you?
GoTrack Webhooks will help you receive the tracking data based on the event type whenever that event occurs.

For example, an update event on a Tracking will trigger our webhook and hit the user-provided API.

How to set up GoTrack Webhook?
Ask GoComet Support Team to enable the webhook feature for you. 

Once enabled for your company, go to Users → Preferences → GoTrack → Configurations page. You will be able to see the Webhook Configurations below


Tracking Configuration page
 

Create a webhook endpoint on your server as an HTTPS endpoint (URL). You need to create 2 API endpoints with the same URL - 

GET - this is to verify that we are able to connect with the server and URL is valid 

PATCH - this will push the JSON data on your app.

Register and Verify your URL in the space provided under Webhook Configuration.

If the hit on GET API for the URL fails, you will get a Not Verified status with an error in the info tag


Not Verified URL
 

If the hit on GET API for the URL succeeds with 2xx status, you will get a Verified status with the last verified time in the info tag


Verified URL
 

To enable receiving the event of tracking updation enable the toggle Tracking Update Event so that tracking data is pushed on your PATCH API whenever a Tracking is updated.


GoComet Tracking Update Webhook
Whenever any info is updated at the tracking level (which may include all containers falling under it), the webhook will push the tracking data on your app as JSON payload if any status, events, stats, or any other info is modified internally by GoComet or externally by User.

Response to this event will be as follows:


Example payload of tracking_update event webhook 