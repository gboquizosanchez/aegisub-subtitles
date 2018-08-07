 Upload a new file and parse it: 
 
 ```Ass::parse(collect(explode("\r\n", File::get($request->file, 'UTF-8'))));```
