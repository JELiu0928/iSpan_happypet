window.onload = ()=>{
    let goodsDescription = document.getElementById('goods_description')
    let mainImg = document.getElementById('main_img')
    let goodsTitle = document.getElementById('goods_title')
    let addCartBtn = document.getElementById('add_cart')
    let currentPage = document.querySelector('li[aria-current="page"]')
    let flavorOrColorArea = document.querySelector('.flavorOrColorArea')
    let weightOrSizeArea = document.querySelector('.weightOrSizeArea')
    // let thumbnail = document.querySelector('.thumbnail')
    // let pdDescImg = document.querySelector('.pdDescImg')
    // let choose = document.querySelectorAll('.choose')

    let myModal = document.getElementById('myModal')
    let myInput = document.getElementById('myInput')
    myModal.addEventListener('shown.bs.modal', function () {
        myInput.focus()
    })
    function showMsg(msg){
        $('#myModal').modal('show')
        $('#alertMsg').text(msg)
    }

    let pdNavbar = document.querySelector('.pd_navbar');

    widthCheck() //初始化
    function widthCheck(){
        window.innerWidth <= 1198 ? pdNavbar.style.top = "148px" :  pdNavbar.style.top = "96px"
        // 監聽window寬度
        window.addEventListener('resize',function(){
            window.innerWidth <= 1198 ? pdNavbar.style.top = "148px" :  pdNavbar.style.top = "96px"
        })
    }
   
    // ------------------------------------------
    // 獲取url 參數
    let urlParams  = new URLSearchParams(window.location.search)
    // console.log('window',window.location.search)
    let categoryID = urlParams.get('category') 
    let seriesID = urlParams.get('sID') 

    // console.log('flavorOrColorArea',flavorOrColorArea.className)
    // fetch(`http://localhost/testpet/public/product/ds/96`,{
    // 查詢此產品資訊
    // fetch(`http://localhost/testpet/public/product/${categoryID}/${seriesID}`,{
    fetch(`http://localhost/happypet/happypet_back/public/api/product/${categoryID}/${seriesID}`,{
        method:'get',
    })
    .then(response=>{
        // return response.text()   //圖片
        return response.json()  //陣列
    })
    .then(data=>{
        // console.log(data)
        let {products,productImgs,categoryName} = data
        // console.log('第一個',products)
        // console.log('第一個',productImgs)
        // console.log('第一個',categoryName)

        // 過濾下架產品
        // products = products.filter(product => product.status === 't');
        // products = products.filter(product => product.status == 1);
        products = products.filter(product => product.shelves_status == 1);
        // console.log('/product/${categoryID}/${seriesID}',products)
        if (products.length === 0) {
            alert('產品準備中');    
            return;
        }

        // console.log(productImgs)
        $('.QuantityArea').toggleClass('d-none d-flex')
        $('.breadcrumb').removeClass("d-none")
        $('.choose').removeClass("d-none")
    
    
        // 取系列名到標題、麵包屑
        // console.log('麵包屑 =>>',$('.breadcrumb').find('a'))
        // console.log($('.breadcrumb').find('a')[1].innerText)
        currentPage.innerText = products[0].series_name
        $('.breadcrumb').find('a').eq(1).text(categoryName) //分類
        goodsTitle.innerText = products[0].series_name
        goodsTitle.setAttribute('data-categoryID',products[0].category_id)
        mainImg.src = products[0].cover_img
        let categoryID = goodsTitle.getAttribute('data-categoryid')
        $('.breadcrumb').find('a').eq(1).prop('href',`http://localhost/happypet/happypet_front/40_product/front/product.html?category=${categoryID}`)
       
        productImgs.forEach((productImg,i)=>{
            let {pic_category_id} = productImg
            if(pic_category_id == 1 || pic_category_id == 2){
                let pdImg = $('<img>').prop('src',productImg.img)
                $('.thumbnail').append(pdImg)
                pdImg.click(function(){
                    $("#main_img").prop('src',$(this).attr('src'))  
                })
                
            }else{
                $('.pdDescImg').append(`<img class="col-12" src="${productImg.img}" alt="">`)
                // pdDescImg.innerHTML += `<img class="col-12" src="${productImg.img}" alt="">`
            }
        })
        //  數量增減
        let currentQuantity = ''
        $('#plusBtn').on('click',()=>{
            currentQuantity = parseInt(pdQuantity.value) + 1
            pdQuantity.value = currentQuantity
        })
        $('#minusBtn').on('click',()=>{
            currentQuantity = parseInt(pdQuantity.value) -1
            pdQuantity.value = currentQuantity
            pdQuantity.value >= 1 || (pdQuantity.value = 1) 
            // console.log(pdQuantity.value)

        })
        // plusBtn.onclick = ()=>{
        //     currentQuantity = parseInt(pdQuantity.value) + 1
        //     pdQuantity.value = currentQuantity
        //     // console.log(pdQuantity.value)
        // }
        // minusBtn.onclick = ()=>{
        //     currentQuantity = parseInt(pdQuantity.value) -1
        //     pdQuantity.value = currentQuantity
        //     pdQuantity.value >= 1 || (pdQuantity.value = 1)  
        //     // console.log(pdQuantity.value)
        // }
        
        // 敘述取出放入Set => 轉Array => forEach
        let descriptionSet = new Set()
        products.forEach((product) => {
            // console.log('descriptionSet',product)
            for(let i = 1; i <= 5 ; i++){
                let key = `description${i}`
                if(key.startsWith('description')){ 
                    descriptionSet.add(product[key])
                }
            }
        });
        console.log('descriptionSet',descriptionSet)
        let descriptionArr = Array.from(descriptionSet)
        // console.log(descriptionArr)
        descriptionArr.forEach((description)=>{
            // console.log(description)
            // let descriptionli = document.createElement('li')
            // descriptionli.innerText = description
            // goodsDescription.appendChild(descriptionli)
            let descriptionli = $('<li>').text(description)
            $("#goods_description").append(descriptionli)
        })

        // 根據取的資料變成選項按鈕
        // let flavorArr = products.reduce(function(arr,{flavor,...items}){
        // arr拿來儲存結果 {flavor}：取出products中每個flavor
        let flavorArr = products.reduce(function(arr,{flavor}){
            // console.log('arr',arr)
            // return arr.indexOf(flavor) == -1 ? [...arr,flavor] : arr
            if (flavor && arr.indexOf(flavor) == -1){
                arr.push(flavor);
            } 
            return arr //用來下次迭代
        },[])
        // console.log('flavorArr',flavorArr)
        
        let styleArr = products.reduce(function(arr,{style}){
            if (style && arr.indexOf(style) == -1){
                arr.push(style);
            }
            return arr
        },[])
        let weightArr = products.reduce(function(arr,{weight}){
            if (weight && arr.indexOf(weight) == -1){
                arr.push(weight);
            }
            return arr
        },[])
        let sizeArr = products.reduce(function(arr,{size}){
            if (size && arr.indexOf(size) == -1){
                arr.push(size);
            }
            return arr
        },[])
        

        if(flavorArr.length > 0){
            traverseArray(flavorArr,flavorOrColorArea,"flavor")
        }else if(styleArr.length > 0){
            traverseArray(styleArr,flavorOrColorArea,"style")
        }
        if(weightArr.length > 0){
            traverseArray(weightArr,weightOrSizeArea,"weight")
        }else if(sizeArr.length > 0){
            traverseArray(sizeArr,weightOrSizeArea,"size")
        }

        // 新增選擇按鈕( 口味、款式 | 淨重、尺寸 )
        function traverseArray(arr,area,type){
            arr.forEach((item,i)=>{
                area.innerHTML += ` 
                    <input type="radio" class="d-none" id="${area.className}${i}" class="flavorOrColor" name="${type}" value="${item}" >
                    <label for="${area.className}${i}">${item}</label>
                `
            })
        }
        
        let productPrice = document.querySelector('#price span')
        let flavorOrColor;
        let weightOrSize;

        if(categoryID == 'ds' || categoryID == 'cs'){
            // flavorOrColor = 'color'
            flavorOrColor = 'style'
            weightOrSize = 'size'
        }else{
            flavorOrColor = 'flavor'
            weightOrSize = 'weight'
        }
        // console.log('種類',flavorOrColor,weightOrSize)
        document.querySelectorAll('.flavorOrColorArea input,.weightOrSizeArea input').forEach(input => {
            input.addEventListener('change',checkInput)
            
        });
        
        // 取得產品資訊後新增購物車
        function getProductInfo(user,productID,quantity){
            let addCartText = document.querySelector('#add_cart p')
            let addCartIcon = document.querySelector('#add_cart i.bi-cart-fill')
            let addCartCheckIcon = document.querySelector('#add_cart i.bi-cart-check-fill')

            // console.log('fn裡的productID',productID)
            // console.log('fn裡的quantity',quantity)
            if(!productID || !quantity){
                console.log('尚未選擇產品',productID,quantity)
                showMsg('尚未選擇產品')
                setTimeout(() => {
                    $('#myModal').modal('hide')
                }, 1500);
            }else{
                addCartText.style.opacity = "0"
                addCartIcon.style.opacity = "1"
                console.log('我是user',user,'我是productID',productID,'我是數量',quantity)
                // fetch(`http://localhost/happypet/happypet_back/public/api/product/insert/${user}/${productID}/${quantity}`,{
                fetch(`http://localhost/happypet/happypet_back/public/api/productcart/${user}/${productID}/${quantity}`,{
                // fetch(`http://localhost/happypet/happypet_back/public/api/product/insert/${productID}/${quantity}`,{
                    method:'get'
                })
                .then(response=>response.text())
                .then(rows =>{
                    console.log('插入資料',rows )
                    if(rows || rows > 0 ){
                        // console.log('執行動畫======',rows )
                        setTimeout(() => {
                            addCartIcon.style.opacity = "0"
                            addCartCheckIcon.style.opacity = "1"
                        }, 1000);
                        setTimeout(() => {
                            addCartCheckIcon.style.opacity = "0"
                            addCartText.style.opacity = "1"
                        }, 2500);
                        queryQuantity(user)
                    }
                })
            }
        }

        console.log('id',localStorage.getItem("uid"))
        if(localStorage.getItem("uid")){
            queryQuantity(localStorage.getItem("uid"))
        }else{
            localStorage.removeItem("uid")
            localStorage.removeItem("cartQuantity")
        }
        // 更新購物車數量(紅點圖標數量)
        // function queryQuantity(){
            // fetch('http://localhost/happypet/happypet_back/public/api/productcart/1')
        function queryQuantity(user){
            $.get(`http://localhost/happypet/happypet_back/public/api/productcart/${user}`)
                .done((quantity)=>{
                    console.log('done',quantity)
                    if(!quantity || quantity == 0){
                        // cartQuantity.style.display = 'none'
                        $('.nav_icon .cart_quantity').addClass('d-none');
                    }else{
                        // cartQuantity.style.display = 'block'
                        $('.nav_icon .cart_quantity').removeClass('d-none');
                        $('.nav_icon .cart_quantity').text(quantity);
                        localStorage.setItem("cartQuantity", quantity);
                        // console.log("購物車quantity localStorage",localStorage.getItem("cartQuantity"))
                    }
                })
            // fetch(`http://localhost/happypet/happypet_back/public/api/productcart/${user}`)
            // .then(response=>response.text())
            // .then(quantity=>{
            //     console.log('購物車quantity',quantity)

            //     if(!quantity || quantity == 0){
            //         // cartQuantity.style.display = 'none'
            //         $('.nav_icon .cart_quantity').addClass('d-none');
            //     }else{
            //         // cartQuantity.style.display = 'block'
            //         $('.nav_icon .cart_quantity').removeClass('d-none');
            //         $('.nav_icon .cart_quantity').text(quantity);
            //         localStorage.setItem("cartQuantity", quantity);
            //         // console.log("購物車quantity localStorage",localStorage.getItem("cartQuantity"))
            //     }
            // })
        }
        // console.log(localStorage.getItem("cartQuantity"))
        let productID;
        // 選擇按鈕後更新價格
        function checkInput(){
            // 被選擇的按鈕
            let selectFlavorOrColor = document.querySelector('.flavorOrColorArea input[type="radio"]:checked')?.value
            let selectWeightOrSize = document.querySelector('.weightOrSizeArea input[type="radio"]:checked')?.value
            // 被選擇的產品
            let selectedProduct = products.find(product=>{
                console.log('selectedProduct的產品',product[flavorOrColor] === selectFlavorOrColor)
                // console.log('product[flavorOrColor]',product[flavorOrColor])
                // console.log('selectFlavorOrColor',selectFlavorOrColor)
                // console.log('product[weightOrSize]',product[weightOrSize])
                // console.log('selectWeightOrSize',selectWeightOrSize)
                // if(selectFlavorOrColor){
                    // console.log('product[flavorOrColor] === selectFlavorOrColor',product[flavorOrColor] === selectFlavorOrColor)
                // }
                // console.log('product[weightOrSize] === selectWeightOrSize', product[weightOrSize] === selectWeightOrSize)
                return product[flavorOrColor] === selectFlavorOrColor && product[weightOrSize] === selectWeightOrSize
            })
            // console.log('selectFlavorOrColor',selectFlavorOrColor)
            // console.log('selectWeightOrSize',selectWeightOrSize)
            productPrice.innerText = selectedProduct.price.toLocaleString()
            console.log('xxxxxxxx',selectedProduct)
            console.log('xxxxxxxx',selectedProduct.product_id)
            
            productID = selectedProduct.product_id || null
            console.log('productID',productID)
            $('#price').removeClass("d-none")
        }

        /**************************************************************************************************************/
        let allFlavorOrColorInputs = document.querySelectorAll('.flavorOrColorArea input[type="radio"]')
        allFlavorOrColorInputs.forEach(input => {
            input.addEventListener('input',findDisableInput)
        });
        let allWeightOrSizeInputs = document.querySelectorAll('.weightOrSizeArea input[type="radio"]') 
        allWeightOrSizeInputs.forEach(input => {
            input.addEventListener('input',findDisableInput)
        });
        /**************************************************************************************************************/

        // 使下架的產品的input框disabled，搭配一開始產品filter(剃除狀態為f-下架產品)
        function findDisableInput(){
            let selectFlavorOrColorValue = document.querySelector('.flavorOrColorArea input[type="radio"]:checked')?.value
            let selectWeightOrSizeValue = document.querySelector('.weightOrSizeArea input[type="radio"]:checked')?.value
            // if(!selectFlavorOrColorValue || !selectWeightOrSizeValue) return //如果沒被選擇就return

            let allFlavorOrColorInputs = document.querySelectorAll('.flavorOrColorArea input[type="radio"]')
            let allWeightOrSizeInputs = document.querySelectorAll('.weightOrSizeArea input[type="radio"]') 
            // 對所有款式檢查，如果沒有
            allFlavorOrColorInputs.forEach((input)=>{
                // 檢查是否有任意產品的 flavorOrColor 屬性值與該按鈕的值匹配，且其 weightOrSize 屬性值與當前選擇的重量或尺寸值匹配。
                console.log('allFlavorOrColorInputs',input)
                let flag = true //預設所有選項禁用
                products.forEach((product)=>{ 
                    // 資料庫寵物睡窩[水上樂園] === input選擇水上樂園 
                    if(product[flavorOrColor] === input.value){
                        // 且沒有被選擇的size || 資料庫寵物睡窩[XL] ==== 選擇的XL
                        if(!selectWeightOrSizeValue || product[weightOrSize] === selectWeightOrSizeValue){
                            flag = false
                        }
                    }
                    // console.log('!selectWeightOrSizeValue ',!selectWeightOrSizeValue,"||","product[weightOrSize] ",product[weightOrSize] ,'=== selectWeightOrSizeValue = ',selectWeightOrSizeValue)
                    // return product[flavorOrColor] === input.value && (!selectWeightOrSizeValue || product[weightOrSize] === selectWeightOrSizeValue) 
                })
                input.disabled = flag;
                // input.disabled = isDisabledStatus;
                // console.log('input.disabled',input.disabled)
            })
            allWeightOrSizeInputs.forEach((input)=>{
                let flag = true //預設所有選項禁用
                // 檢查是否有任意產品的 weightOrSize 屬性值與該按鈕的值匹配，且其 flavorOrColor 屬性值與當前選擇的口味或顏色值 (selectedFlavorOrColorValue) 匹配。
                // input.disabled = !products.some((product)=>{ 
                products.forEach((product)=>{ 
                    if(product[weightOrSize] === input.value){
                        if(!selectFlavorOrColorValue || product[flavorOrColor] === selectFlavorOrColorValue){
                            flag = false
                        }
                    }
                    // return product[weightOrSize] === input.value && product[flavorOrColor] === selectFlavorOrColorValue 
                    // return product[weightOrSize] === input.value && (!selectFlavorOrColorValue || product[flavorOrColor] === selectFlavorOrColorValue);
                })
                input.disabled = flag;
                // input.disabled = isDisabledStatus;
                // console.log('input.disabled',input.disabled)
            })
        }
        // 點選加入購物車
        addCartBtn.onclick = ()=>{
            if(!localStorage.getItem("uid")){
                showMsg('尚未登入')
            }else{
                getProductInfo(localStorage.getItem("uid"), productID, currentQuantity ? currentQuantity : pdQuantity.value)
                queryQuantity(localStorage.getItem("uid"))
            }
        }    
     
    })
    .catch(error => {
        console.error('Error:', error);
    });
    
}