# location-voiture-api

A complete and modern vehicle reservation system built with Symfony and API Platform, featuring robust business logic and secure authentication.


## 🚀 Installation and Running the Project

1. Clone the repository:
   
```sh
git clone https://github.com/elmehdizouihar/location-voiture-api.git
```

2. Install dependencies:

```sh
composer install
```

3. Create the `.env` file:

```sh
cp .env.example .env
```

4. Create the database:

```sh
php bin/console doctrine:database:create
```

5. create the tables

```sh
php bin/console doctrine:migrations:migrate
```

6. Run the Project

```sh
symfony serve
```

## 🛠️ Technologies Used

PHP 8

Symfony 7

API Platform

JWT Authentication

Doctrine ORM

## 📁 Project Structure

```sh
src/ 
├── Controller/ 
│   ├── AuthController.php 
│   ├── CarController.php 
│   └── ReservationController.php 
├── Entity/ 
│   ├── User.php 
│   ├── Car.php 
│   └── Reservation.php 
└── Service/ 
    ├── Reservation/ 
    │   ├── ReservationAvailabilityChecker.php 
    │   ├── ReservationDeletionManager.php 
    │   ├── ReservationManager.php 
    │   ├── UserReservationManager.php 
    └── Validation/ 
        └── ReservationValidator.php 
```

## 🔌 API Endpoints

### Authentication

#### Login

```sh
POST /api/login 
Content-Type: application/json 

{ 
  "email": "user@example.com", 
  "password": "password" 
} 

Success Response (200): 
{ 
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." 
} 

Error Response (401): 
{ 
    "code": 401, 
    "message": "Invalid credentials." 
} 
```

### Cars

#### List all cars

```sh
GET /api/cars 
Authorization: Bearer <token> 

[ 
  { 
      "id": 1, 
      "brand": "Toyota", 
      "model": "Corolla", 
      "registrationNumber": "AB-123-CD", 
      "dailyRate": 50.00,
  },
  {
      "id": 2,
      "brand": "Peugeot",
      "model": "208",
      "registrationNumber": "BC-456-EF",
      "dailyRate": 45.00
  },
  {
      "id": 3,
      "brand": "Renault",
      "model": "Clio",
      "registrationNumber": "CD-789-GH",
      "dailyRate": 48.00
  },
  {
      "id": 4,
      "brand": "Volkswagen",
      "model": "Golf",
      "registrationNumber": "DE-321-IJ",
      "dailyRate": 60.00
  },
]
```

#### Displaying Car Details

```sh
GET /api/cars/1
Authorization: Bearer <token>


Success Response (200):

{
    "id": 1,
    "brand": "Toyota",
    "model": "Corolla",
    "registrationNumber": "AB-123-CD",
    "dailyRate": 50.00,
}
```

### Reservations

#### Create new reservation

```sh
POST /api/reservations
Authorization: Bearer <token>
Content-Type: application/json

{
    "user": "/api/users/1",
    "car": "/api/cars/12",
    "startDate": "2025-09-03 10:00:00",
    "endDate": "2025-09-04 18:00:00"
}

Success Response (201):

{
    "id": 6,
    "user": "/api/users/1",
    "car": {
      "id": 1,
      "brand": "Toyota",
      "model": "Corolla",
      "registrationNumber": "AB-123-CD",
      "dailyRate": 50.00,
    },
    "startDate": "2025-09-03T10:00:00+02:00",
    "endDate": "2025-09-04T18:00:00+02:00",
    "createdAt": "2025-05-14T21:58:27+02:00",
    "updatedAt": null
}

if the car is already reserved

Error Response (400):

{
    "message": "The car is already booked for these dates."
}

If the end date is higher from the start date

Error Response (400):

{
    "message": "The end date must be greater than the start date"
}

```

#### Update reservation

```sh
Put /api/reservations/4
Authorization: Bearer <token>
Content-Type: application/json

{
    "user": "/api/users/1",
    "car": "/api/cars/1",
    "startDate": "2025-07-03 10:00:00",
    "endDate": "2025-07-04 20:00:00"
}

Success Response (200):

{
    "id": 6,
    "user": "/api/users/1",
    "car": {
      "id": 1,
      "brand": "Toyota",
      "model": "Corolla",
      "registrationNumber": "AB-123-CD",
      "dailyRate": 50.00,
    },
    "startDate": "2025-07-03T10:00:00+02:00",
    "endDate": "2025-07-04T10:00:00+02:00",
    "createdAt": "2025-05-14T21:58:27+02:00",
    "updatedAt": "2025-05-14T22:09:11+02:00"
}

if the car is already reserved between these dates

Error Response (400):

{
    "message": "The car is already booked for these dates."
}

if a user wants to modify another user's reservation

Error Response (403):

{
    "message": "You cannot modify a reservation that does not belong to you."
}
```

#### Displaying all of a User's Reservations

```sh
GET /api/users/1/reservations
Authorization: Bearer <token>

Success Response (200):

[
    {
        "id": 6,
        "user": "/api/users/1",
        "car": {
          "id": 1,
          "brand": "Toyota",
          "model": "Corolla",
          "registrationNumber": "AB-123-CD",
          "dailyRate": 50.00
        },
        "startDate": "2025-09-03T10:00:00+02:00",
        "endDate": "2025-09-04T10:00:00+02:00",
        "createdAt": "2025-05-14T21:58:27+02:00",
        "updatedAt": null
    },
    {
        "id": 5,
        "user": "/api/users/1",
        "car": {
          "id": 2,
          "brand": "Peugeot",
          "model": "208",
          "registrationNumber": "BC-456-EF",
          "dailyRate": 45.00
        },
        "startDate": "2025-07-03T10:00:00+02:00",
        "endDate": "2025-07-04T10:00:00+02:00",
        "createdAt": "2025-05-14T21:05:39+02:00",
        "updatedAt": null
    },
    {
        "id": 2,
        "user": "/api/users/1",
        "car": {
            "id": 4,
            "brand": "Volkswagen",
            "model": "Golf",
            "registrationNumber": "DE-321-IJ",
            "dailyRate": 60.00
        },
        "startDate": "2025-05-26T10:00:00+02:00",
        "endDate": "2025-05-27T16:00:00+02:00",
        "createdAt": "2025-05-14T20:33:10+02:00",
        "updatedAt": null
    }
]

```

#### Delete Reservations

```sh
DELETE /api/reservations/1
Authorization: Bearer <token>

Success Response (200):

1

if a user wants to delete a reservation from another user
Error Response (403):

{
    "message": "You cannot delete a reservation that does not belong to you"
}
```
