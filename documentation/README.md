The following is a brief documentation of the endpoints of the API and the return values that will be returned from the API. This is a work in progress, so please be patient.

# Authentication

NOTE: Unless otherwise noted, endpoints require Basic Authentication. You can implement this by passing a base64-encoded string of username password. Example HTTP header:

```
Authorization: Basic base64(username:password)
```

An example of a HTTP header with a user with username someone@google.com and password *thisismypassword*:

```
Authorization: Basic c29tZW9uZUBnb29nbGUuY29tOnRoaXNpc215cGFzc3dvcmQ=
```

the following link can be used to base64 encode strings: https://www.base64encode.org/