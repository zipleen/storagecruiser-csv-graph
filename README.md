Trying to make some graphs from really big csv files that come from storagecruiser
-----

So, if you know storagecruiser from fujitsu, you'll know that it can only take 5 graphs at a time... 
We wanted more graphs, but we wanted to make some filters and build some graphs based on the properties provided.

This is a really HACKED scripts, made in one afternoon just to help make this csv's.

Mysql
----
So I though, ok lets put this in a sql database and it will be faster. So I made a insert script to search the requested
directory and insert data from the csv's. The first atempt at doing that with mysql proved to be very SLOW (really really slow
and it had stupid performance problems if i restarted to insert more lines) - anyway I switched to postgresql. 

Postgresql
----------
In pg, it was faster but still very slow - it was taking a lot of time to insert the 35 million rows - thinking about it, 35 million
rows is a lot of data =)
So, instead of inserting it via PHP, I used PHP to create a csv that would insert all the values through a COPY command. Insertdata.php now
creates a bla.txt in shared memory (for the 35million rows it was a 1.2gb file i think). Then we use
cat bla.txt | psql -U user -h localhost -c "COPY data(id_dev,id_prop,data,valor) FORM STDIN WITH CSV HEADER;" sf
This proved to be the fastest way of inserting data to PG - this very slow, so I had a full database inside the shared memory storage - this
gave a performance boost, but still too slow!

I had to make due and do the csv, the getcsv.php file took around 20minutes to output a csv file (this was not a simple select, it was a select
with a sub-query for each property chosen!) 

The getcsv2.php was just a modification to support multiple values while adding columns.


