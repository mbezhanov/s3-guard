# S3 Guard

Inspired by [s3auth](https://github.com/yegor256/s3auth), **S3 Guard** is an application that allows you to serve static website content hosted in private Amazon S3 buckets over HTTPS, secured with Basic HTTP Auth.

**S3 Guard** is a PHP application, built with the [Laravel](https://laravel.com/) framework.

## Installation

**S3 Guard** is meant to be used as a self-hosted service. The easiest way to install it, is by using one of the official images published on [Docker Hub](https://hub.docker.com/r/mbezhanov/s3-guard/). One installation is capable of running multiple websites.

Adding the following service definition to your Docker [stack file](https://docs.docker.com/docker-cloud/apps/stack-yaml-reference/) or [compose file](https://docs.docker.com/compose/compose-file/) is sufficient to get started:  

```yaml
services:
  s3guard:
    image: mbezhanov/s3-guard:1.0.0
    environment:
      # change this on your environment:
      - APP_KEY=base64:eCV9KE+OPquLOxCCVv08crz4l3+RXmu++YJ6JHgyvoo=
```

The **APP_KEY** environment variable is used for specifying an encryption key for securing the cookie and session data coming out of your application. **S3 Guard** won't run without a properly specified **APP_KEY**. You can read more about generating one [here](https://laravel.com/docs/5.6/installation#configuration).

## Usage

**S3 Guard** can host multiple websites. To host a single static website secured by HTTP Basic Authentication:

1. Create a new Amazon S3 bucket, where the static files will be stored. ([how?](https://docs.aws.amazon.com/AmazonS3/latest/gsg/CreatingABucket.html)) 

    Note that you don't need to grant public read access to this bucket, and you don't need to enable static website hosting for that bucket.

2. Upload all files to the newly created bucket. Make sure the `index.html` file is in the root dir of your bucket. ([how?](https://docs.aws.amazon.com/AmazonS3/latest/gsg/PuttingAnObjectInABucket.html))

3. Create a new IAM user with programmatic access (AWS Management Console access is not needed), and write down the generated **access key ID** and **secret access key**. ([how?](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_users_create.html#id_users_create_console))

    Then, attach a policy (managed or inline) that allows that user to read files from the bucket that holds the website files.

    ```json
    {
        "Version": "2012-10-17",
        "Statement": [
            {
                "Effect": "Allow",
                "Action": ["s3:GetObject", "s3:GetBucketWebsite"],
                "Resource": [
                    "arn:aws:s3:::your.bucket.name/*"
                ]
            }
        ]
    }
    ```

4. Configure the host that you plan to serve files for via S3 Guard, in NGINX. You can use the following configuration file as a starting point:

    ```
    server {
        listen 443 ssl;
        server_name foo.example.com;
        ssl_certificate /etc/letsencrypt/live/foo.example.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/foo.example.com/privkey.pem;
    
        location / {
            fastcgi_pass s3guard:9000;
            fastcgi_index index.php;
            fastcgi_buffers 16 16k;
            fastcgi_buffer_size 32k;
            fastcgi_param SCRIPT_FILENAME /s3-guard/public/index.php;
            include fastcgi_params;
        }
    }
    ```

5. Add the host to S3 Guard:

    ```bash
    root@s3guard:~# docker container ps | grep s3guard
    85507c5b5e4c        mbezhanov/s3-guard:1.0.0   "docker-php-entrypoi…"   About an hour ago   Up About an hour    9000/tcp                                   root_s3guard_1
    root@s3guard:~# docker container exec -it root_s3guard_1 ash
    /s3-guard # php artisan host:add
    
     Hostname (e.g. test.example.com):
     > foo.example.com
    
     HTTP Auth Username:
     > foo
    
     HTTP Auth Password:
     >
    
     S3 Bucket Name (e.g. my.s3.bucket):
     > your.bucket.name
    
     AWS Key (20 symbols):
     > XUROAQTMXHAIKWLZJ7EA
    
     AWS Secret Key (40 symbols):
     > znyltiz4mWUnWRWHiRz5LJsiLYmCsbYayXFSY8ru
    
     AWS Region Name (e.g. us-west-1):
     > eu-central-1
    
    Host was added successfully
    ```

6. Navigate to your website. You should be prompted for a username and password, and upon successfull authentication - see the static website hosted in your S3 Bucket.
 