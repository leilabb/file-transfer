Project from 2016

Programs required:
Xampp (Apache 2.4.17, PHP 5.6.19, MySQL 5.0.11)
Database creation:
Database name: transactions

Create the tables in the following order:
Name: file
Column Type
Filename Primary Key,
varchar 255,
not null
TotalNumberOfRows Int, nullable
RowsWithErrors int, nullable
StartDate timestamp,
nullable
EndDate timestamp,
nullable
CreationDate timestamp

Name: transaction_description
Column Type
TransactionDescriptionPK Primary Key,

int, Auto-
increment

Description Varchar(33),
unique
CreationDate timestamp
Name: transaction
Column Type
TransactionID Primary Key, int
TransactionDate date
Amount float
CreationDate timestamp
TransactionDescriptionPK int, indexed, Foreign Key

to
TransactionDescriptionPK
in table
‘transaction_description’
FilePK varchar, indexed, Foreign
Key to Filename in table
‘file’

Name: all_transactions
Column Type
TransactionID Primary Key,
int
TransactionDate date
Amount float
Description varchar(33)
StartDate timestamp
EndDate timestamp
Filename varchar 255
