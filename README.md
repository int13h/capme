## capME!

Easy bake oven for sguil transcripts

## Provides

* cliscript.tcl which can be used from the command line to generate a transcript
* a web front end that will let you:
       
1) Fill in form fields and click a button to get a transcript or,

2) Automagically populate and submit the form by supplying the fields in the URI:
      
`https://host.ca/capme/index.php?sip=10.10.10.1&spt=4242&dip=10.10.10.2&dpt=80&ts=2012-11-27%2005:34:00&usr=paulh&pwd=aBcDeF`
 

## Notes

 * If no sid is supplied the script takes a peek in the sancp table to find an appropriate one.  
 * If you aren't using securityonion then two sguild libs need minor patches for this to work. Take a peek in the patches folder.
