#Pixel-Style Avatar Generator
![Avatar Example](graphics/examples/avatar-0.png?raw=true "Avatar Example")

I needed a kid-friendly (G rated) avatar generator, preferably PHP based. Inspired by the randomly generated github avatars, but unable to find a satisfactory pre-made solution, I did what I always do and took on the challenge from scratch. Here's the results of my simple PG-rated pixel-style avatar generator, with billions of possible combinations!

![Avatar Example 1](graphics/examples/avatar-1.png?raw=true "Avatar Example 1")
![Avatar Example 2](graphics/examples/avatar-2.png?raw=true "Avatar Example 2")

![Avatar Example 3](graphics/examples/avatar-3.png?raw=true "Avatar Example 3")
![Avatar Example 4](graphics/examples/avatar-4.png?raw=true "Avatar Example 4")

##Usage
It should be pretty straightforward to use-

```
// Avatar::render($size = 400, $gender = null, $id = null)
Avatar::render(200, 'male', 'some-reusable-identifier');
```
By using an identifier, you can re-generate the identical avatar by sending the same identifier (and gender) later. For my own purposes size is limited to 512 max width/height for performance reasons, but if you plan to generate and cache the avatars, you can go as big as you like!

If you want to use the output rather than render directly to the output stream, use `Avatar::generate` which returns the image resource.

##Photoshop file included
Under `graphics/` you'll find `avatars.psd`, a complete photoshop file that produced the layers using the image assets generator. You can add and remove layers, regenerate the layers and then replace the `images/` folder to use your own set. Just make sure the file names begin with the layer name. include `_m`, `_f`, or `_mf` at the end of the file name to designate the layer for Males, Females, or both respectively.

The generator is set to 20x20px layer sizes. If you create a brand new set with a different resolution you'll want to change the `AVATAR_SIZE` constant in avatar.php.

##More Examples
![Avatar Examples](graphics/examples/avatars.gif?raw=true "Avatar Examples")

Enjoy!

Licensed under the MIT licence. See LICENSE for details.
